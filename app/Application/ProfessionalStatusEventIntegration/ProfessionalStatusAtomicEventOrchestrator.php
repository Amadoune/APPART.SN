<?php

namespace App\Application\ProfessionalStatusEventIntegration;

use App\Application\ProfessionalStatusEventIntegration\Contract\ProfessionalStatusAtomicTransaction;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
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
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEvent;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventCatalog;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventMetadata;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayloadVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\Contract\ProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualInspectionStatus;
use Throwable;

final readonly class ProfessionalStatusAtomicEventOrchestrator
{
    public function __construct(
        private ProfessionalStatusOrchestrator $orchestrator,
        private ProfessionalStatusContextualReplayInspector $inspector,
        private ProfessionalStatusAtomicTransaction $transaction,
        private ProfessionalStatusEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private PublicProjectionOutboxWriter $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function transition(ProfessionalStatusAtomicEventRequest $request): ProfessionalStatusOrchestrationResult
    {
        try {
            return $this->transaction->run(function () use ($request): ProfessionalStatusOrchestrationResult {
                $result = $this->orchestrator->execute($request->transitionRequest());
                if ($result->status === ProfessionalStatusOrchestrationStatus::PersistenceCorrupted) {
                    throw new ProfessionalStatusEventIntegrationFailure('Professional status persistence failed.');
                }
                if (! in_array($result->status, [ProfessionalStatusOrchestrationStatus::Applied, ProfessionalStatusOrchestrationStatus::AlreadyApplied], true)) {
                    return $result;
                }

                $inspection = $this->inspector->inspectLatest($request->professionalId);
                if ($inspection->status !== ProfessionalStatusContextualInspectionStatus::Found || $inspection->snapshot === null) {
                    throw new ProfessionalStatusEventIntegrationFailure('Persisted professional status transition cannot be inspected.');
                }
                $transition = $inspection->snapshot->transition;
                $occurredVersion = $inspection->snapshot->version;
                $type = $this->events->typeFor($transition);
                $payloadVersion = ProfessionalStatusEventPayloadVersion::V1;
                $eventId = ProfessionalStatusEventId::derive($type, $payloadVersion, $request->professionalId, $transition, $occurredVersion);
                $event = new ProfessionalStatusEvent(
                    new ProfessionalStatusEventMetadata($type, $payloadVersion, $request->context->actor, $request->context->occurredAt, $request->recordedAt),
                    new ProfessionalStatusEventPayload($eventId, $request->professionalId, implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]), $transition->from, $transition->to, $transition->action, $payloadVersion->value, $occurredVersion),
                );
                $fact = new PublicProjectionDeliveryPublishableFact(
                    PublicProjectionDeliveryEventType::fromString($type->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt($payloadVersion->value),
                    PublicProjectionDeliverySourceModule::fromString('Professionals'),
                    PublicProjectionDeliveryAggregateType::fromString('ProfessionalStatus'),
                    PublicProjectionDeliveryAggregateId::fromString($request->professionalId->value),
                    new PublicProjectionDeliveryOrder($occurredVersion, PublicProjectionDeliveryEventIndex::fromInt(1)),
                    $request->context->occurredAt->value,
                    new ProfessionalStatusDeliveryPayload($event),
                );
                $written = $this->outbox->append($this->messages->create($fact, $request->recordedAt->value), $this->consumerId);
                if (! in_array($written, [PublicProjectionOutboxWriteResult::Applied, PublicProjectionOutboxWriteResult::AlreadyApplied], true)) {
                    throw new ProfessionalStatusEventIntegrationFailure('Professional status event Outbox write was rejected.');
                }

                return $result;
            });
        } catch (Throwable) {
            return new ProfessionalStatusOrchestrationResult(ProfessionalStatusOrchestrationStatus::PersistenceCorrupted);
        }
    }
}
