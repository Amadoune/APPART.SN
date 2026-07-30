<?php

namespace Tests\Unit\Modules\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Application\UseCase\ArchiveListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Application\UseCase\ExpireListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\PublishListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\RejectListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\RequestChanges;
use Appart\Modules\ListingLifecycle\Application\UseCase\SendToReview;
use Appart\Modules\ListingLifecycle\Application\UseCase\SubmitListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\SuspendListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\WithdrawListing;
use Appart\Modules\ListingLifecycle\Domain\Exception\ConcurrentListingModification;
use Appart\Modules\ListingLifecycle\Domain\Exception\TransitionConditionNotSatisfied;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
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
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\ListingLifecycle\Support\FakeListingRegistry;
use Tests\Unit\Modules\ListingLifecycle\Support\FakeMediaCatalog;
use Tests\Unit\Modules\ListingLifecycle\Support\FakePropertyCatalog;

final class ListingUseCasesTest extends TestCase
{
    private FakeListingRegistry $registry;

    private FakePropertyCatalog $properties;

    private ListingTransitionPolicy $policy;

    private FakeMediaCatalog $media;

    private int $sequence = 1;

    protected function setUp(): void
    {
        $this->registry = new FakeListingRegistry;
        $this->properties = new FakePropertyCatalog;
        $this->properties->set($this->propertyId(), PropertyAvailability::Eligible);
        $this->policy = new ListingTransitionPolicy;
        $this->media = new FakeMediaCatalog;
        $this->media->set($this->collectionId(), PublicationMediaAvailability::Eligible);
    }

    public function test_all_use_cases_orchestrate_and_save_the_complete_lifecycle(): void
    {
        $this->create();
        (new SubmitListing($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, 1));
        (new SendToReview($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation, 2));
        (new RequestChanges($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::CorrectableIssues, TransitionOrigin::Moderation, 3));
        (new SubmitListing($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::CorrectionsCompleted, TransitionOrigin::Advertiser, 4));
        (new SendToReview($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation, 5));
        (new PublishListing($this->registry, $this->properties, $this->policy, $this->media))->execute($this->listingId(), $this->collectionId(), $this->revision(), ExpirationDate::fromDateTime($this->at(100)), $this->evidence(TransitionTrigger::FavorableReview, TransitionOrigin::Moderation, 6));
        (new SuspendListing($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::RiskDetected, TransitionOrigin::Moderation, 7));
        (new PublishListing($this->registry, $this->properties, $this->policy, $this->media))->execute($this->listingId(), $this->collectionId(), $this->revision(), ExpirationDate::fromDateTime($this->at(200)), $this->evidence(TransitionTrigger::RegularizationValidated, TransitionOrigin::Moderation, 8));
        (new ExpireListing($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::PublicationDeadlineReached, TransitionOrigin::System, 200));
        (new WithdrawListing($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::VoluntaryWithdrawal, TransitionOrigin::Advertiser, 201));
        (new ArchiveListing($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::ReactivationDeadlineReached, TransitionOrigin::System, 300));
        self::assertSame(ListingStatus::Archived, $this->registry->find($this->listingId())?->status());
        self::assertSame([], $this->registry->find($this->listingId())?->releaseEvents());
    }

    public function test_reject_use_case_is_saved(): void
    {
        $this->create();
        $submit = new SubmitListing($this->registry, $this->properties, $this->policy);
        $submit->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, 1));
        (new SendToReview($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation, 2));
        (new RejectListing($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::NonRegularizableContent, TransitionOrigin::Moderation, 3));
        self::assertSame(ListingStatus::Rejected, $this->registry->find($this->listingId())?->status());
    }

    #[DataProvider('unavailableProperties')]
    public function test_explicit_property_states_block_creation(PropertyAvailability $availability): void
    {
        $this->properties->set($this->propertyId(), $availability);
        $this->expectException(TransitionConditionNotSatisfied::class);
        $this->create();
    }

    public static function unavailableProperties(): array
    {
        return [[PropertyAvailability::Missing], [PropertyAvailability::Ineligible], [PropertyAvailability::Archived], [PropertyAvailability::Unavailable]];
    }

    public function test_failed_save_leaves_detached_snapshot_unchanged(): void
    {
        $this->create();
        $this->registry->failNextSave();
        try {
            (new SubmitListing($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, 1));
            self::fail('Save should fail.');
        } catch (ConcurrentListingModification) {
            self::assertSame(ListingStatus::Draft, $this->registry->find($this->listingId())?->status());
            self::assertSame(0, $this->registry->find($this->listingId())?->version());
        }
    }

    private function create(): void
    {
        (new CreateDraft($this->registry, $this->properties, $this->policy))->execute($this->listingId(), $this->propertyId(), $this->revision(), $this->evidence(TransitionTrigger::DraftStarted, TransitionOrigin::Advertiser, 0));
    }

    private function evidence(TransitionTrigger $t, TransitionOrigin $o, int $m): TransitionEvidence
    {
        return new TransitionEvidence(ActorId::fromString('actor-1'), $t, TransitionReason::fromString('Motif métier obligatoire'), $o, $this->at($m));
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('70000000-0000-4000-8000-000000000001');
    }

    private function propertyId(): PropertyId
    {
        return PropertyId::fromString('50000000-0000-4000-8000-000000000001');
    }

    private function collectionId(): MediaCollectionId
    {
        return MediaCollectionId::fromString('60000000-0000-4000-8000-000000000001');
    }

    private function revision(): ListingRevisionId
    {
        return ListingRevisionId::fromString(sprintf('70000000-0000-4000-8000-%012x', $this->sequence++));
    }

    private function at(int $m): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00')->modify("+{$m} minutes");
    }
}
