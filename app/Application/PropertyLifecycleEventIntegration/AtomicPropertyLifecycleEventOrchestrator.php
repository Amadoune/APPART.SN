<?php

namespace App\Application\PropertyLifecycleEventIntegration;

use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleAtomicTransaction;
use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleEventOrchestrator;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleDeliveryPayload;
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
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationDiagnosticCode;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationStatus;
use DateTimeImmutable;
use Throwable;

final readonly class AtomicPropertyLifecycleEventOrchestrator implements PropertyLifecycleEventOrchestrator
{
    public function __construct(
        private PropertyLifecycleOrchestrator $orchestrator,
        private PropertyLifecycleAtomicTransaction $transaction,
        private PropertyLifecycleEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private PublicProjectionOutboxWriter $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function transition(PropertyLifecycleEventOrchestrationRequest $request): PropertyLifecycleOrchestrationResult
    {
        try {
            return $this->transaction->run(function () use ($request): PropertyLifecycleOrchestrationResult {
                $result = $this->orchestrator->transition($request->transitionRequest());
                if (! in_array($result->status, [PropertyLifecycleOrchestrationStatus::Applied, PropertyLifecycleOrchestrationStatus::AlreadyApplied], true) || $result->transition === null) {
                    return $result;
                }
                $events = $this->events->eventsFor(
                    $request->propertyId,
                    $result->transition,
                    $request->expectedVersion + 1,
                    $request->metadata(),
                );
                foreach ($events as $index => $event) {
                    $fact = new PublicProjectionDeliveryPublishableFact(
                        PublicProjectionDeliveryEventType::fromString($event->type->value),
                        PublicProjectionDeliveryPayloadVersion::fromInt($event->payloadVersion->value),
                        PublicProjectionDeliverySourceModule::fromString('RealEstateCatalog'),
                        PublicProjectionDeliveryAggregateType::fromString('Property'),
                        PublicProjectionDeliveryAggregateId::fromString($event->payload->propertyId->value),
                        new PublicProjectionDeliveryOrder($event->payload->lifecycleVersion, PublicProjectionDeliveryEventIndex::fromInt($index + 1)),
                        new DateTimeImmutable($event->metadata->occurredAt->value),
                        new PropertyLifecycleDeliveryPayload($event),
                    );
                    $message = $this->messages->create($fact, new DateTimeImmutable($event->metadata->recordedAt->value));
                    $written = $this->outbox->append($message, $this->consumerId);
                    if (! in_array($written, [PublicProjectionOutboxWriteResult::Applied, PublicProjectionOutboxWriteResult::AlreadyApplied], true)) {
                        throw new PropertyLifecycleEventIntegrationFailure('Property lifecycle event Outbox write was rejected.');
                    }
                }

                return $result;
            });
        } catch (Throwable) {
            return PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure);
        }
    }
}
