<?php

namespace App\Application\AdministrativeActionLifecycleEventIntegration;

use App\Application\AdministrativeActionLifecycleEventIntegration\Contract\AdministrativeActionLifecycleAtomicTransaction;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
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
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEvent;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventCatalog;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventMetadata;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayloadVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\Contract\AdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualInspectionStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Throwable;

final readonly class AdministrativeActionLifecycleAtomicEventOrchestrator
{
    public function __construct(
        private AdministrativeActionLifecycleOrchestrator $orchestrator,
        private AdministrativeActionContextualReplayInspector $inspector,
        private AdministrativeActionLifecycleAtomicTransaction $transaction,
        private AdministrativeActionLifecycleEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private PublicProjectionOutboxWriter $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function transition(
        AdministrativeActionLifecycleAtomicEventRequest $request,
    ): AdministrativeActionLifecycleOrchestrationResult {
        try {
            return $this->transaction->run(function () use ($request): AdministrativeActionLifecycleOrchestrationResult {
                $result = $this->orchestrator->execute($request->transitionRequest());
                if ($result->status === AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted) {
                    throw new AdministrativeActionLifecycleEventIntegrationFailure('Administrative Action Lifecycle persistence failed.');
                }
                if (! in_array($result->status, [
                    AdministrativeActionLifecycleOrchestrationStatus::Applied,
                    AdministrativeActionLifecycleOrchestrationStatus::AlreadyApplied,
                ], true)) {
                    return $result;
                }

                $inspection = $this->inspector->inspectLatest($request->actionId);
                if ($inspection->status !== AdministrativeActionContextualInspectionStatus::Found
                    || $inspection->snapshot === null) {
                    throw new AdministrativeActionLifecycleEventIntegrationFailure('Persisted Administrative Action Lifecycle transition cannot be inspected.');
                }

                $transition = $inspection->snapshot->transition;
                $occurredVersion = $inspection->snapshot->version;
                $type = $this->events->typeFor($transition);
                $payloadVersion = AdministrativeActionLifecycleEventPayloadVersion::V1;
                $eventId = AdministrativeActionLifecycleEventId::derive(
                    $type,
                    $payloadVersion,
                    $request->actionId,
                    $transition,
                    $occurredVersion,
                );
                $event = new AdministrativeActionLifecycleEvent(
                    new AdministrativeActionLifecycleEventMetadata(
                        $type,
                        $payloadVersion,
                        $inspection->snapshot->context->actor,
                        $inspection->snapshot->context->occurredAt,
                        $request->recordedAt,
                    ),
                    new AdministrativeActionLifecycleEventPayload(
                        $eventId,
                        $request->actionId,
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
                    PublicProjectionDeliverySourceModule::fromString('AdministrationAudit'),
                    PublicProjectionDeliveryAggregateType::fromString('AdministrativeActionLifecycle'),
                    PublicProjectionDeliveryAggregateId::fromString($request->actionId->value),
                    new PublicProjectionDeliveryOrder(
                        $occurredVersion,
                        PublicProjectionDeliveryEventIndex::fromInt(1),
                    ),
                    $inspection->snapshot->context->occurredAt->value,
                    new AdministrativeActionLifecycleDeliveryPayload($event),
                );
                $written = $this->outbox->append(
                    $this->messages->create($fact, $request->recordedAt->value),
                    $this->consumerId,
                );
                if (! in_array($written, [
                    PublicProjectionOutboxWriteResult::Applied,
                    PublicProjectionOutboxWriteResult::AlreadyApplied,
                ], true)) {
                    throw new AdministrativeActionLifecycleEventIntegrationFailure('Administrative Action Lifecycle event Outbox write was rejected.');
                }

                return $result;
            });
        } catch (Throwable) {
            return new AdministrativeActionLifecycleOrchestrationResult(
                AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted,
            );
        }
    }
}
