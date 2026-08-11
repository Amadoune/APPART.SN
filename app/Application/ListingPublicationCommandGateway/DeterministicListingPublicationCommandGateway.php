<?php

namespace App\Application\ListingPublicationCommandGateway;

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
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\PublicationExpiration\Contract\ListingPublicationExpirationPolicyV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandLedgerV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationGatewayTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\Contract\ListingRevisionAllocatorV1;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionIntentId;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionOperation;
use Appart\Modules\ListingLifecycle\Application\UseCase\PublishListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\SendToReview;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Application\Ownership\MediaOwnershipResolution;
use Appart\Modules\Media\Domain\ValueObject\PropertyId as MediaPropertyId;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

final readonly class DeterministicListingPublicationCommandGateway implements ListingPublicationCommandGatewayV1
{
    public function __construct(
        private ListingPublicationGatewayTransaction $transaction,
        private ListingPublicationCommandLedgerV1 $ledger,
        private ListingPublicationWorkflowStore $workflow,
        private ListingRegistry $listings,
        private ListingRevisionAllocatorV1 $revisions,
        private ListingPublicationExpirationPolicyV1 $expiration,
        private MediaCollectionOwnershipLookup $mediaOwnership,
        private SendToReview $sendToReview,
        private PublishListing $publishListing,
        private ListingPublicationOrchestrator $workflowOrchestrator,
        private ListingPublicationEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private PublicProjectionOutboxWriter $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function beginReview(string $listingId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): ListingPublicationCommandResultV1
    {
        return $this->execute('begin_review', ListingPublicationAction::BeginReview, $listingId, $commandId, $expectedVersion, $actor, $occurredAt);
    }

    public function approveAndPublish(string $listingId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): ListingPublicationCommandResultV1
    {
        return $this->execute('approve_and_publish', ListingPublicationAction::ApproveAndPublish, $listingId, $commandId, $expectedVersion, $actor, $occurredAt);
    }

    private function execute(string $operation, ListingPublicationAction $action, string $listingId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): ListingPublicationCommandResultV1
    {
        $checksum = hash('sha256', json_encode([$operation, $listingId, $commandId, $expectedVersion, $actor, $occurredAt->format('Y-m-d\TH:i:s.uP')], JSON_THROW_ON_ERROR));
        try {
            return $this->transaction->run(function () use ($operation, $action, $listingId, $commandId, $expectedVersion, $actor, $occurredAt, $checksum): ListingPublicationCommandResultV1 {
                $existing = $this->ledger->find($commandId);
                if ($existing !== null) {
                    if (! hash_equals($existing->checksum, $checksum)) {
                        return new ListingPublicationCommandResultV1(ListingPublicationCommandStatus::DivergentCommand, $existing->workflowVersion, $existing->aggregateVersion);
                    }

                    return new ListingPublicationCommandResultV1(
                        $existing->status === ListingPublicationCommandStatus::Applied ? ListingPublicationCommandStatus::AlreadyApplied : $existing->status,
                        $existing->workflowVersion,
                        $existing->aggregateVersion,
                    );
                }
                if (! $this->ledger->reserve($commandId, $listingId, $operation, $checksum, $occurredAt)) {
                    return new ListingPublicationCommandResultV1(ListingPublicationCommandStatus::VersionConflict);
                }

                $id = ListingId::fromString($listingId);
                $stored = $this->workflow->read($id);
                $aggregate = $this->listings->find($id);
                if ($stored->status !== ListingPublicationPersistenceReadStatus::Found || $stored->snapshot === null || $aggregate === null) {
                    return $this->complete($commandId, $checksum, ListingPublicationCommandStatus::Missing, $stored->snapshot?->version, $aggregate?->version());
                }
                if ($stored->snapshot->version !== $expectedVersion) {
                    return $this->complete($commandId, $checksum, ListingPublicationCommandStatus::VersionConflict, $stored->snapshot->version, $aggregate->version());
                }
                $expectedWorkflowState = $action === ListingPublicationAction::BeginReview ? ListingPublicationState::Submitted : ListingPublicationState::UnderReview;
                $expectedAggregateState = $action === ListingPublicationAction::BeginReview ? ListingStatus::Submitted : ListingStatus::UnderReview;
                if ($stored->snapshot->state !== $expectedWorkflowState || $aggregate->status() !== $expectedAggregateState) {
                    return $this->complete($commandId, $checksum, ListingPublicationCommandStatus::StateConflict, $stored->snapshot->version, $aggregate->version());
                }

                $instant = $occurredAt->setTimezone(new DateTimeZone('UTC'));
                $evidence = new TransitionEvidence(
                    ActorId::fromString($actor),
                    $action === ListingPublicationAction::BeginReview ? TransitionTrigger::ReviewStarted : TransitionTrigger::FavorableReview,
                    null,
                    TransitionOrigin::Moderation,
                    $instant,
                );
                $revision = $this->revisions->allocate(
                    $id,
                    $action === ListingPublicationAction::BeginReview ? ListingRevisionOperation::BeginReview : ListingRevisionOperation::ApproveAndPublish,
                    ListingRevisionIntentId::fromString($commandId),
                );

                $workflowResult = $this->workflowOrchestrator->transition(new ListingPublicationOrchestrationRequest($id, $action, $expectedVersion));
                if (! in_array($workflowResult->status, [ListingPublicationOrchestrationStatus::Applied, ListingPublicationOrchestrationStatus::AlreadyApplied], true) || $workflowResult->transition === null) {
                    return $this->complete($commandId, $checksum, ListingPublicationCommandStatus::TransitionDenied, $stored->snapshot->version, $aggregate->version());
                }

                if ($action === ListingPublicationAction::BeginReview) {
                    $this->sendToReview->execute($id, $revision, $evidence);
                } else {
                    $ownership = $this->mediaOwnership->resolve(MediaPropertyId::fromString($aggregate->propertyId()->value));
                    if ($ownership->resolution !== MediaOwnershipResolution::Found || $ownership->collectionId === null) {
                        throw new ListingPublicationAuthorityUnavailable;
                    }
                    $this->publishListing->execute(
                        $id,
                        MediaCollectionId::fromString($ownership->collectionId->value),
                        $revision,
                        $this->expiration->expirationFor($instant),
                        $evidence,
                    );
                }

                $this->appendEvent($id, $workflowResult->transition, $expectedVersion + 1, $instant);
                $saved = $this->listings->find($id);
                if ($saved === null) {
                    throw new ListingPublicationGatewayFailure;
                }

                return $this->complete($commandId, $checksum, ListingPublicationCommandStatus::Applied, $expectedVersion + 1, $saved->version());
            });
        } catch (ListingPublicationAuthorityUnavailable) {
            return new ListingPublicationCommandResultV1(ListingPublicationCommandStatus::AuthorityUnavailable);
        } catch (Throwable) {
            return new ListingPublicationCommandResultV1(ListingPublicationCommandStatus::DependencyUnavailable);
        }
    }

    private function complete(string $commandId, string $checksum, ListingPublicationCommandStatus $status, ?int $workflowVersion, ?int $aggregateVersion): ListingPublicationCommandResultV1
    {
        if (! $this->ledger->complete($commandId, $checksum, $status, $workflowVersion, $aggregateVersion)) {
            throw new ListingPublicationGatewayFailure;
        }

        return new ListingPublicationCommandResultV1($status, $workflowVersion, $aggregateVersion);
    }

    private function appendEvent(ListingId $listingId, ListingPublicationTransition $transition, int $version, DateTimeImmutable $at): void
    {
        $canonical = ListingPublicationEventInstant::fromCanonicalUtc($at->format('Y-m-d\TH:i:s.u\Z'));
        $metadata = new ListingPublicationEventMetadata($canonical, $canonical);
        foreach ($this->events->eventsFor($listingId, $transition, $version, $metadata) as $index => $event) {
            $fact = new PublicProjectionDeliveryPublishableFact(
                PublicProjectionDeliveryEventType::fromString($event->type->value),
                PublicProjectionDeliveryPayloadVersion::fromInt($event->payloadVersion->value),
                PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'),
                PublicProjectionDeliveryAggregateType::fromString('Listing'),
                PublicProjectionDeliveryAggregateId::fromString($listingId->value),
                new PublicProjectionDeliveryOrder($version, PublicProjectionDeliveryEventIndex::fromInt($index + 1)),
                $at,
                new ListingPublicationDeliveryPayload($event),
            );
            $message = $this->messages->create($fact, $at);
            $written = $this->outbox->append($message, $this->consumerId);
            if (! in_array($written, [PublicProjectionOutboxWriteResult::Applied, PublicProjectionOutboxWriteResult::AlreadyApplied], true)) {
                throw new ListingPublicationGatewayFailure;
            }
        }
    }
}

final class ListingPublicationGatewayFailure extends \RuntimeException {}

final class ListingPublicationAuthorityUnavailable extends \RuntimeException {}
