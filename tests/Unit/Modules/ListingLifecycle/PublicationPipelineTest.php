<?php

namespace Tests\Unit\Modules\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Application\UseCase\PublishListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\SendToReview;
use Appart\Modules\ListingLifecycle\Application\UseCase\SubmitListing;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingPublished;
use Appart\Modules\ListingLifecycle\Domain\Exception\ConcurrentListingModification;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingException;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingNotFound;
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

final class PublicationPipelineTest extends TestCase
{
    private FakeListingRegistry $listings;

    private FakePropertyCatalog $properties;

    private FakeMediaCatalog $media;

    private ListingTransitionPolicy $policy;

    private int $revisionSequence = 1;

    protected function setUp(): void
    {
        $this->listings = new FakeListingRegistry;
        $this->properties = new FakePropertyCatalog;
        $this->media = new FakeMediaCatalog;
        $this->policy = new ListingTransitionPolicy;
        $this->properties->set($this->propertyId(), PropertyAvailability::Eligible);
        $this->media->set($this->collectionId(), PublicationMediaAvailability::Eligible);
        $this->prepareListingUnderReview();
        $this->listings->saveCalls = 0;
    }

    public function test_publication_succeeds_and_produces_only_listing_published(): void
    {
        $this->publish();

        $listing = $this->listings->find($this->listingId());
        self::assertSame(ListingStatus::Published, $listing?->status());
        self::assertNotNull($listing?->expirationDate());
        self::assertSame(1, $this->listings->saveCalls);
        self::assertSame(1, $this->media->reads);
        self::assertCount(1, $this->listings->lastSavedEvents);
        self::assertInstanceOf(ListingPublished::class, $this->listings->lastSavedEvents[0]);
        self::assertSame([], $listing?->releaseEvents(), 'Persisted snapshots must not retain events.');
    }

    #[DataProvider('unavailableMedia')]
    public function test_media_preconditions_fail_without_side_effect(PublicationMediaAvailability $availability): void
    {
        $this->media->set($this->collectionId(), $availability);

        $this->assertRejectedWithoutSideEffect();
    }

    public static function unavailableMedia(): array
    {
        return [
            'collection absent' => [PublicationMediaAvailability::Missing],
            'no active media' => [PublicationMediaAvailability::WithoutActiveMedia],
            'no primary media' => [PublicationMediaAvailability::WithoutPrimaryMedia],
            'collection belongs to another property' => [PublicationMediaAvailability::PropertyMismatch],
        ];
    }

    public function test_absent_property_fails_without_side_effect(): void
    {
        $this->properties->set($this->propertyId(), PropertyAvailability::Missing);
        $this->assertRejectedWithoutSideEffect();
    }

    public function test_absent_listing_fails_before_other_catalog_reads(): void
    {
        $this->listings = new FakeListingRegistry;

        try {
            $this->publish();
            self::fail('Publication should have been rejected.');
        } catch (ListingNotFound) {
            self::assertSame(0, $this->listings->saveCalls);
            self::assertSame(0, $this->media->reads);
        }
    }

    public function test_incompatible_listing_fails_without_side_effect(): void
    {
        $freshListings = new FakeListingRegistry;
        (new CreateDraft($freshListings, $this->properties, $this->policy))->execute($this->listingId(), $this->propertyId(), $this->revision(), $this->evidence(TransitionTrigger::DraftStarted, TransitionOrigin::Advertiser, 0));
        $this->listings = $freshListings;
        $this->listings->saveCalls = 0;

        $this->assertRejectedWithoutSideEffect(ListingStatus::Draft);
    }

    public function test_failed_atomic_save_exposes_neither_state_nor_event(): void
    {
        $this->listings->failNextSave();

        try {
            $this->publish();
            self::fail('The save was expected to fail.');
        } catch (ConcurrentListingModification) {
            $listing = $this->listings->find($this->listingId());
            self::assertSame(ListingStatus::UnderReview, $listing?->status());
            self::assertSame([], $listing?->releaseEvents());
            self::assertNull($listing?->expirationDate());
        }
    }

    private function assertRejectedWithoutSideEffect(ListingStatus $expected = ListingStatus::UnderReview): void
    {
        try {
            $this->publish();
            self::fail('Publication should have been rejected.');
        } catch (ListingException) {
            $listing = $this->listings->find($this->listingId());
            self::assertSame($expected, $listing?->status());
            self::assertSame(0, $this->listings->saveCalls);
            self::assertSame([], $listing?->releaseEvents());
            self::assertNull($listing?->expirationDate());
        }
    }

    private function publish(): void
    {
        (new PublishListing($this->listings, $this->properties, $this->policy, $this->media))->execute(
            $this->listingId(),
            $this->collectionId(),
            $this->revision(),
            ExpirationDate::fromDateTime($this->at(120)),
            $this->evidence(TransitionTrigger::FavorableReview, TransitionOrigin::Moderation, 3),
        );
    }

    private function prepareListingUnderReview(): void
    {
        (new CreateDraft($this->listings, $this->properties, $this->policy))->execute($this->listingId(), $this->propertyId(), $this->revision(), $this->evidence(TransitionTrigger::DraftStarted, TransitionOrigin::Advertiser, 0));
        (new SubmitListing($this->listings, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, 1));
        (new SendToReview($this->listings, $this->properties, $this->policy))->execute($this->listingId(), $this->revision(), $this->evidence(TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation, 2));
    }

    private function evidence(TransitionTrigger $trigger, TransitionOrigin $origin, int $minute): TransitionEvidence
    {
        return new TransitionEvidence(ActorId::fromString('actor-1'), $trigger, TransitionReason::fromString('Motif metier obligatoire'), $origin, $this->at($minute));
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
        return ListingRevisionId::fromString(sprintf('70000000-0000-4000-8000-%012x', $this->revisionSequence++));
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-18T12:00:00+00:00')->modify("+{$minute} minutes");
    }
}
