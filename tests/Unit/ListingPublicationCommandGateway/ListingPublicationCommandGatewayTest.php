<?php

namespace Tests\Unit\ListingPublicationCommandGateway;

use App\Application\ListingPublicationCommandGateway\DeterministicListingPublicationCommandGateway;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use Appart\Modules\ListingLifecycle\Application\PublicationExpiration\NinetyDayListingPublicationExpirationPolicy;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandLedgerV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationGatewayTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandLedgerRecord;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationStoredState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\DeterministicListingRevisionAllocatorV1;
use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Application\UseCase\PublishListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\SendToReview;
use Appart\Modules\ListingLifecycle\Application\UseCase\SubmitListing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PublicationMediaAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Application\Ownership\MediaOwnershipResult;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId as OwnedMediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId as MediaPropertyId;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\ListingLifecycle\Support\FakeListingRegistry;
use Tests\Unit\Modules\ListingLifecycle\Support\FakeMediaCatalog;
use Tests\Unit\Modules\ListingLifecycle\Support\FakePropertyCatalog;

final class ListingPublicationCommandGatewayTest extends TestCase
{
    public function test_begin_approve_and_replay_keep_workflow_and_aggregate_synchronized(): void
    {
        $id = ListingId::fromString('11111111-1111-4111-8111-111111111111');
        $propertyId = PropertyId::fromString('22222222-2222-4222-8222-222222222222');
        $collectionId = MediaCollectionId::fromString('33333333-3333-4333-8333-333333333333');
        $registry = new FakeListingRegistry;
        $properties = new FakePropertyCatalog;
        $properties->set($propertyId, PropertyAvailability::Eligible);
        $media = new FakeMediaCatalog;
        $media->set($collectionId, PublicationMediaAvailability::Eligible);
        $policy = new ListingTransitionPolicy;
        $at = new DateTimeImmutable('2026-08-11T10:00:00+00:00');
        (new CreateDraft($registry, $properties, $policy))->execute($id, $propertyId, ListingRevisionId::fromString('44444444-4444-4444-8444-444444444444'), $this->evidence($at, TransitionTrigger::DraftStarted, TransitionOrigin::Advertiser));
        (new SubmitListing($registry, $properties, $policy))->execute($id, ListingRevisionId::fromString('55555555-5555-4555-8555-555555555555'), $this->evidence($at->modify('+1 minute'), TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser));

        $workflow = new MemoryWorkflowStore($id);
        $orchestrator = new DeterministicListingPublicationOrchestrator(new ListingPublicationWorkflow, $workflow);
        $orchestrator->transition(new ListingPublicationOrchestrationRequest($id, ListingPublicationAction::Submit, 1));
        $ledger = new MemoryGatewayLedger;
        $ownership = $this->createMock(MediaCollectionOwnershipLookup::class);
        $ownership->method('resolve')->willReturn(MediaOwnershipResult::found(MediaPropertyId::fromString($propertyId->value), OwnedMediaCollectionId::fromString($collectionId->value)));
        $outbox = $this->createMock(PublicProjectionOutboxWriter::class);
        $outbox->method('append')->willReturn(PublicProjectionOutboxWriteResult::Applied);
        $gateway = new DeterministicListingPublicationCommandGateway(
            new PassthroughGatewayTransaction,
            $ledger,
            $workflow,
            $registry,
            new DeterministicListingRevisionAllocatorV1,
            new NinetyDayListingPublicationExpirationPolicy,
            $ownership,
            new SendToReview($registry, $properties, $policy),
            new PublishListing($registry, $properties, $policy, $media),
            $orchestrator,
            new ListingPublicationEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $outbox,
            PublicProjectionOutboxConsumerId::fromString('public-projection-updater'),
        );

        $begin = $gateway->beginReview($id->value, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 2, 'reviewer', $at->modify('+2 minutes'));
        self::assertSame(ListingPublicationCommandStatus::Applied, $begin->status);
        self::assertSame(ListingStatus::UnderReview, $registry->find($id)?->status());
        self::assertSame(ListingPublicationState::UnderReview, $workflow->state);

        $approve = $gateway->approveAndPublish($id->value, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 3, 'reviewer', $at->modify('+3 minutes'));
        self::assertSame(ListingPublicationCommandStatus::Applied, $approve->status);
        self::assertSame(ListingStatus::Published, $registry->find($id)->status());
        self::assertSame(ListingPublicationState::Published, $workflow->state);
        self::assertSame(ListingPublicationCommandStatus::AlreadyApplied, $gateway->approveAndPublish($id->value, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 3, 'reviewer', $at->modify('+3 minutes'))->status);
        self::assertSame(ListingPublicationCommandStatus::DivergentCommand, $gateway->approveAndPublish($id->value, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 3, 'other-reviewer', $at->modify('+3 minutes'))->status);
    }

    private function evidence(DateTimeImmutable $at, TransitionTrigger $trigger, TransitionOrigin $origin): TransitionEvidence
    {
        $reason = in_array($trigger, [TransitionTrigger::ReviewStarted, TransitionTrigger::FavorableReview], true)
            ? null
            : TransitionReason::fromString('Certified test intent');

        return new TransitionEvidence(ActorId::fromString('actor'), $trigger, $reason, $origin, $at);
    }
}

final class PassthroughGatewayTransaction implements ListingPublicationGatewayTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}

final class MemoryGatewayLedger implements ListingPublicationCommandLedgerV1
{
    /** @var array<string, array{checksum:string,status:ListingPublicationCommandStatus,workflow:?int,aggregate:?int}> */
    private array $records = [];

    public function find(string $commandId): ?ListingPublicationCommandLedgerRecord
    {
        $record = $this->records[$commandId] ?? null;

        return $record === null ? null : new ListingPublicationCommandLedgerRecord($commandId, $record['checksum'], $record['status'], $record['workflow'], $record['aggregate']);
    }

    public function reserve(string $commandId, string $listingId, string $operation, string $checksum, DateTimeImmutable $occurredAt): bool
    {
        if (isset($this->records[$commandId])) {
            return false;
        }
        $this->records[$commandId] = ['checksum' => $checksum, 'status' => ListingPublicationCommandStatus::DependencyUnavailable, 'workflow' => null, 'aggregate' => null];

        return true;
    }

    public function complete(string $commandId, string $checksum, ListingPublicationCommandStatus $status, ?int $workflowVersion, ?int $aggregateVersion): bool
    {
        $this->records[$commandId] = ['checksum' => $checksum, 'status' => $status, 'workflow' => $workflowVersion, 'aggregate' => $aggregateVersion];

        return true;
    }
}

final class MemoryWorkflowStore implements ListingPublicationWorkflowStore
{
    public ListingPublicationState $state = ListingPublicationState::Draft;

    public int $version = 1;

    public function __construct(private readonly ListingId $id) {}

    public function initialize(ListingId $listingId, ListingPublicationState $state): ListingPublicationPersistenceWriteResult
    {
        return ListingPublicationPersistenceWriteResult::AlreadyApplied;
    }

    public function append(ListingId $listingId, ListingPublicationTransition $transition, int $version): ListingPublicationPersistenceWriteResult
    {
        if ($version !== $this->version + 1 || $transition->from !== $this->state) {
            return ListingPublicationPersistenceWriteResult::RejectedVersion;
        }
        $this->state = $transition->to;
        $this->version = $version;

        return ListingPublicationPersistenceWriteResult::Applied;
    }

    public function read(ListingId $listingId): ListingPublicationPersistenceReadResult
    {
        return ListingPublicationPersistenceReadResult::found(new ListingPublicationStoredState($this->id, $this->state, $this->version));
    }
}
