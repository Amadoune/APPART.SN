<?php

namespace Tests\Unit\Contracts\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use Tests\Unit\Modules\ListingLifecycle\Support\FakeListingRegistry;

class FakeListingRegistryHarness implements ListingRegistryHarness
{
    public function freshRegistry(): ListingRegistry
    {
        return new FakeListingRegistry;
    }

    public function minimalListing(?ListingId $id = null): Listing
    {
        return Listing::createDraft(
            $id ?? $this->primaryId(),
            PropertyId::fromString('32000000-0000-4000-8000-000000000001'),
            $this->revision(1),
            $this->evidence(TransitionTrigger::DraftStarted, TransitionOrigin::Advertiser, 0),
            new ListingTransitionPolicy,
            PropertyAvailability::Eligible,
        );
    }

    public function listingWithHistory(?ListingId $id = null): Listing
    {
        $listing = $this->minimalListing($id);
        $this->mutate($listing);
        $listing->sendToReview($this->revision(3), $this->evidence(TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation, 2), new ListingTransitionPolicy, PropertyAvailability::Eligible);

        return $listing;
    }

    public function archivedListing(?ListingId $id = null): Listing
    {
        $listing = $this->minimalListing($id);
        $listing->archive($this->revision(4), $this->evidence(TransitionTrigger::RetentionDeadlineReached, TransitionOrigin::System, 1), new ListingTransitionPolicy, PropertyAvailability::Eligible);

        return $listing;
    }

    public function expiredListing(?ListingId $id = null): Listing
    {
        $listing = $this->publishedListing($id);
        $listing->expire($this->revision(5), $this->evidence(TransitionTrigger::PublicationDeadlineReached, TransitionOrigin::System, 5), new ListingTransitionPolicy, PropertyAvailability::Eligible);

        return $listing;
    }

    public function withdrawnListing(?ListingId $id = null): Listing
    {
        $listing = $this->publishedListing($id);
        $listing->withdraw($this->revision(6), $this->evidence(TransitionTrigger::VoluntaryWithdrawal, TransitionOrigin::Advertiser, 4), new ListingTransitionPolicy, PropertyAvailability::Eligible);

        return $listing;
    }

    public function primaryId(): ListingId
    {
        return ListingId::fromString('32000000-0000-4000-8000-000000000101');
    }

    public function distinctId(): ListingId
    {
        return ListingId::fromString('32000000-0000-4000-8000-000000000102');
    }

    public function mutate(Listing $listing): void
    {
        $listing->submit($this->revision(2), $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, 1), new ListingTransitionPolicy, PropertyAvailability::Eligible);
    }

    public function failNextWrite(ListingRegistry $registry): void
    {
        Assert::assertInstanceOf(FakeListingRegistry::class, $registry);
        $registry->failNextSave();
    }

    private function publishedListing(?ListingId $id): Listing
    {
        $listing = $this->listingWithHistory($id);
        $listing->publish(
            $this->revision(4),
            ExpirationDate::fromDateTime(new DateTimeImmutable('2026-07-17T10:04:00+00:00')),
            $this->evidence(TransitionTrigger::FavorableReview, TransitionOrigin::Moderation, 3),
            new ListingTransitionPolicy,
            PropertyAvailability::Eligible,
        );

        return $listing;
    }

    private function revision(int $suffix): ListingRevisionId
    {
        return ListingRevisionId::fromString(sprintf('32000000-0000-4000-8000-%012d', $suffix));
    }

    private function evidence(TransitionTrigger $trigger, TransitionOrigin $origin, int $minute): TransitionEvidence
    {
        return new TransitionEvidence(
            ActorId::fromString('actor:listing-contract'),
            $trigger,
            TransitionReason::fromString('Fixed evidence for the shared Listing Registry contract.'),
            $origin,
            new DateTimeImmutable(sprintf('2026-07-17T10:%02d:00+00:00', $minute)),
        );
    }
}
