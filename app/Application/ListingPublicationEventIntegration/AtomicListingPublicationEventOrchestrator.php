<?php

namespace App\Application\ListingPublicationEventIntegration;

use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationAtomicTransaction;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventTransport\ListingPublicationDeliveryPayload;
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
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use DateTimeImmutable;
use Throwable;

final readonly class AtomicListingPublicationEventOrchestrator implements ListingPublicationEventOrchestrator
{
    public function __construct(
        private ListingPublicationOrchestrator $orchestrator,
        private ListingPublicationAtomicTransaction $transaction,
        private ListingPublicationEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private PublicProjectionOutboxWriter $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function transition(ListingPublicationEventOrchestrationRequest $request): ListingPublicationOrchestrationResult
    {
        try {
            return $this->transaction->run(function () use ($request): ListingPublicationOrchestrationResult {
                $result = $this->orchestrator->transition($request->transition);
                if (! in_array($result->status, [ListingPublicationOrchestrationStatus::Applied, ListingPublicationOrchestrationStatus::AlreadyApplied], true) || $result->transition === null) {
                    return $result;
                }
                $events = $this->events->eventsFor(
                    $request->transition->listingId,
                    $result->transition,
                    $request->transition->expectedVersion + 1,
                    $request->metadata,
                );
                foreach ($events as $index => $event) {
                    $fact = new PublicProjectionDeliveryPublishableFact(
                        PublicProjectionDeliveryEventType::fromString($event->type->value),
                        PublicProjectionDeliveryPayloadVersion::fromInt($event->payloadVersion->value),
                        PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'),
                        PublicProjectionDeliveryAggregateType::fromString('Listing'),
                        PublicProjectionDeliveryAggregateId::fromString($event->payload->listingId->value),
                        new PublicProjectionDeliveryOrder($event->payload->publicationVersion, PublicProjectionDeliveryEventIndex::fromInt($index + 1)),
                        new DateTimeImmutable($event->metadata->occurredAt->value),
                        new ListingPublicationDeliveryPayload($event),
                    );
                    $message = $this->messages->create($fact, new DateTimeImmutable($event->metadata->recordedAt->value));
                    $written = $this->outbox->append($message, $this->consumerId);
                    if (! in_array($written, [PublicProjectionOutboxWriteResult::Applied, PublicProjectionOutboxWriteResult::AlreadyApplied], true)) {
                        throw new ListingPublicationEventIntegrationFailure('Listing publication event Outbox write was rejected.');
                    }
                }

                return $result;
            });
        } catch (Throwable) {
            return ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure);
        }
    }
}
