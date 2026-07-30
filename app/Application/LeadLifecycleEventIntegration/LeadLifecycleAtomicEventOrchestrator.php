<?php

namespace App\Application\LeadLifecycleEventIntegration;

use App\Application\LeadLifecycleEventIntegration\Contract\LeadLifecycleAtomicTransaction;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEvent;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventCatalog;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventMetadata;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayloadVersion;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\Contract\LeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualInspectionStatus;
use Throwable;

final readonly class LeadLifecycleAtomicEventOrchestrator
{
    public function __construct(
        private LeadLifecycleOrchestrator $orchestrator,
        private LeadLifecycleContextualReplayInspector $inspector,
        private LeadLifecycleAtomicTransaction $transaction,
        private LeadLifecycleEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private PublicProjectionOutboxWriter $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function transition(LeadLifecycleAtomicEventRequest $request): LeadLifecycleOrchestrationResult
    {
        try {
            return $this->transaction->run(function () use ($request): LeadLifecycleOrchestrationResult {
                $result = $this->orchestrator->execute($request->transitionRequest());
                if ($result->status === LeadLifecycleOrchestrationStatus::PersistenceCorrupted) {
                    throw new LeadLifecycleEventIntegrationFailure('Lead lifecycle persistence failed.');
                }
                if (! in_array($result->status, [LeadLifecycleOrchestrationStatus::Applied, LeadLifecycleOrchestrationStatus::AlreadyApplied], true)) {
                    return $result;
                }

                $inspection = $this->inspector->inspectLatest($request->leadId);
                if ($inspection->status !== LeadLifecycleContextualInspectionStatus::Found || $inspection->snapshot === null) {
                    throw new LeadLifecycleEventIntegrationFailure('Persisted Lead lifecycle transition cannot be inspected.');
                }
                $transition = $inspection->snapshot->transition;
                $occurredVersion = $inspection->snapshot->version;
                $type = $this->events->typeFor($transition);
                $payloadVersion = LeadLifecycleEventPayloadVersion::V1;
                $eventId = LeadLifecycleEventId::derive($type, $payloadVersion, $request->leadId, $transition, $occurredVersion);
                $event = new LeadLifecycleEvent(
                    new LeadLifecycleEventMetadata($type, $payloadVersion, $request->actor, $request->occurredAt, $request->recordedAt),
                    new LeadLifecycleEventPayload(
                        $eventId,
                        $request->leadId,
                        implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]),
                        $transition->from,
                        $transition->to,
                        $transition->action,
                        $payloadVersion->value,
                        $occurredVersion,
                    ),
                );
                $fact = new PublicProjectionDeliveryPublishableFact(
                    PublicProjectionDeliveryEventType::fromString($type->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt($payloadVersion->value),
                    PublicProjectionDeliverySourceModule::fromString('ContactsLeads'),
                    PublicProjectionDeliveryAggregateType::fromString('LeadLifecycle'),
                    PublicProjectionDeliveryAggregateId::fromString($request->leadId->value),
                    new PublicProjectionDeliveryOrder($occurredVersion, PublicProjectionDeliveryEventIndex::fromInt(1)),
                    $request->occurredAt->value,
                    new LeadLifecycleDeliveryPayload($event),
                );
                $written = $this->outbox->append($this->messages->create($fact, $request->recordedAt->value), $this->consumerId);
                if (! in_array($written, [PublicProjectionOutboxWriteResult::Applied, PublicProjectionOutboxWriteResult::AlreadyApplied], true)) {
                    throw new LeadLifecycleEventIntegrationFailure('Lead lifecycle event Outbox write was rejected.');
                }

                return $result;
            });
        } catch (Throwable) {
            return new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::PersistenceCorrupted);
        }
    }
}
