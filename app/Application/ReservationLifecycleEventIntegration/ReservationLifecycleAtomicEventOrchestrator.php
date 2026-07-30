<?php

namespace App\Application\ReservationLifecycleEventIntegration;

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
use App\Application\ReservationLifecycleEventIntegration\Contract\ReservationLifecycleAtomicTransaction;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\Contract\ReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationStatus;
use Throwable;

final readonly class ReservationLifecycleAtomicEventOrchestrator
{
    public function __construct(
        private ReservationLifecycleEventOrchestrator $orchestrator,
        private ReservationLifecycleAtomicTransaction $transaction,
        private ReservationLifecycleEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private PublicProjectionOutboxWriter $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function transition(ReservationLifecycleAtomicEventRequest $request): ReservationLifecycleOrchestrationResult
    {
        try {
            return $this->transaction->run(function () use ($request): ReservationLifecycleOrchestrationResult {
                $result = $this->orchestrator->transition($request->transitionRequest());
                if ($result->status === ReservationLifecycleOrchestrationStatus::PersistenceCorrupted) {
                    throw new ReservationLifecycleEventIntegrationFailure('Reservation lifecycle persistence failed.');
                }
                if (! in_array($result->status, [ReservationLifecycleOrchestrationStatus::Applied, ReservationLifecycleOrchestrationStatus::AlreadyApplied], true) || $result->transition === null) {
                    return $result;
                }

                $event = $this->events->eventFor($request->reservationId, $result->transition, $request->expectedVersion + 1);
                $payload = new ReservationLifecycleDeliveryPayload($event);
                $fact = new PublicProjectionDeliveryPublishableFact(
                    PublicProjectionDeliveryEventType::fromString($event->metadata->eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt($event->metadata->payloadVersion->value),
                    PublicProjectionDeliverySourceModule::fromString('ReservationLifecycle'),
                    PublicProjectionDeliveryAggregateType::fromString('ReservationLifecycle'),
                    PublicProjectionDeliveryAggregateId::fromString($event->payload->reservationId->value),
                    new PublicProjectionDeliveryOrder($event->payload->occurredVersion, PublicProjectionDeliveryEventIndex::fromInt(1)),
                    $request->occurredAt,
                    $payload,
                );
                $written = $this->outbox->append($this->messages->create($fact, $request->recordedAt), $this->consumerId);
                if (! in_array($written, [PublicProjectionOutboxWriteResult::Applied, PublicProjectionOutboxWriteResult::AlreadyApplied], true)) {
                    throw new ReservationLifecycleEventIntegrationFailure('Reservation lifecycle event Outbox write was rejected.');
                }

                return $result;
            });
        } catch (Throwable) {
            return ReservationLifecycleOrchestrationResult::persistenceCorrupted();
        }
    }
}
