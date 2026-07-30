<?php

namespace App\Application\PlaceLifecycleEventIntegration;

use App\Application\PlaceLifecycleEventIntegration\Contract\PlaceLifecycleAtomicTransaction;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleDeliveryPayload;
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
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventCatalog;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationResult;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationStatus;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrator;

final readonly class PlaceLifecycleAtomicEventOrchestrator
{
    public function __construct(
        private PlaceLifecycleOrchestrator $orchestrator,
        private PlaceLifecycleAtomicTransaction $transaction,
        private PlaceLifecycleEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private PublicProjectionOutboxWriter $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function transition(
        PlaceLifecycleAtomicEventRequest $request,
    ): PlaceLifecycleOrchestrationResult {
        return $this->transaction->run(function () use ($request): PlaceLifecycleOrchestrationResult {
            $result = $this->orchestrator->execute($request->transition);

            if (! in_array($result->status, [
                PlaceLifecycleOrchestrationStatus::Applied,
                PlaceLifecycleOrchestrationStatus::AlreadyApplied,
            ], true)) {
                return $result;
            }

            $transition = new PlaceLifecycleTransition(
                $request->transition->current->state,
                $request->transition->action,
                $result->state,
            );
            $occurredVersion = $request->transition->context->expectedSourceVersion->value + 1;
            $event = $this->events->eventFor(
                $transition,
                $request->transition->context,
                $occurredVersion,
            );
            $fact = new PublicProjectionDeliveryPublishableFact(
                PublicProjectionDeliveryEventType::fromString($event->type->value),
                PublicProjectionDeliveryPayloadVersion::fromInt($event->payload->version->value),
                PublicProjectionDeliverySourceModule::fromString('Geography'),
                PublicProjectionDeliveryAggregateType::fromString('PlaceLifecycle'),
                PublicProjectionDeliveryAggregateId::fromString($event->payload->placeId->value),
                new PublicProjectionDeliveryOrder(
                    $occurredVersion,
                    PublicProjectionDeliveryEventIndex::fromInt(1),
                ),
                $event->occurredAt->value,
                new PlaceLifecycleDeliveryPayload($event),
            );
            $written = $this->outbox->append(
                $this->messages->create($fact, $request->recordedAt),
                $this->consumerId,
            );

            if (! in_array($written, [
                PublicProjectionOutboxWriteResult::Applied,
                PublicProjectionOutboxWriteResult::AlreadyApplied,
            ], true)) {
                throw new PlaceLifecycleAtomicEventIntegrationFailure(
                    'Place Lifecycle event Outbox write was rejected.',
                );
            }

            return $result;
        });
    }
}
