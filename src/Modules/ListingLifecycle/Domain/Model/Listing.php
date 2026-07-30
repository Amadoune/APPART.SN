<?php

namespace Appart\Modules\ListingLifecycle\Domain\Model;

use Appart\Modules\ListingLifecycle\Domain\Event\AbstractListingEvent;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingArchived;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingChangesRequested;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingDraftCreated;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingEvent;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingExpired;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingPublished;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingRejected;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingSentToReview;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingSubmitted;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingSuspended;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingWithdrawn;
use Appart\Modules\ListingLifecycle\Domain\Exception\InvalidListingValue;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingViolation;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PublicationMediaAvailability;

final class Listing
{
    /** @var list<ListingRevision> */
    private array $revisions = [];

    /** @var list<ListingEvent> */
    private array $events = [];

    private int $version = 0;

    private ?ExpirationDate $expirationDate = null;

    private function __construct(private readonly ListingId $id, private readonly PropertyId $propertyId, private ListingStatus $status, private \DateTimeImmutable $lastChangedAt) {}

    public static function createDraft(ListingId $id, PropertyId $propertyId, ListingRevisionId $revisionId, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): self
    {
        $policy->assertDraftCreation($evidence, $property);
        $listing = new self($id, $propertyId, ListingStatus::Draft, $evidence->occurredAt);
        $listing->appendRevision($revisionId, null, ListingStatus::Draft, $evidence);
        $listing->recordEvent(new ListingDraftCreated($id, $revisionId, $propertyId, $evidence), 0);

        return $listing;
    }

    /** @param list<ListingRevision> $revisions */
    public static function reconstitute(ListingId $id, PropertyId $propertyId, ListingStatus $status, \DateTimeImmutable $lastChangedAt, array $revisions, ?ExpirationDate $expirationDate, int $version): self
    {
        if ($version < 0) {
            throw InvalidListingValue::field('version');
        }
        $listing = new self($id, $propertyId, $status, $lastChangedAt);
        $listing->revisions = $revisions;
        $listing->expirationDate = $expirationDate;
        $listing->version = $version;

        return $listing;
    }

    public function submit(ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): void
    {
        $this->apply(ListingStatus::Submitted, $id, $evidence, $policy, $property, ListingSubmitted::class);
    }

    public function sendToReview(ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): void
    {
        $this->apply(ListingStatus::UnderReview, $id, $evidence, $policy, $property, ListingSentToReview::class);
    }

    public function requestChanges(ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): void
    {
        $this->apply(ListingStatus::ChangesRequested, $id, $evidence, $policy, $property, ListingChangesRequested::class);
    }

    public function publish(ListingRevisionId $id, ExpirationDate $expirationDate, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property, PublicationMediaAvailability $media = PublicationMediaAvailability::Eligible): void
    {
        $expirationDate->ensureAfter($evidence->occurredAt);
        $policy->assertPublicationMedia($media);
        $previous = $this->transitionTo(ListingStatus::Published, $id, $evidence, $policy, $property);
        $this->expirationDate = $expirationDate;
        $this->recordEvent(new ListingPublished($this->id, $id, $previous, $expirationDate, $evidence));
    }

    public function suspend(ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): void
    {
        $this->apply(ListingStatus::Suspended, $id, $evidence, $policy, $property, ListingSuspended::class);
    }

    public function expire(ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): void
    {
        if ($this->status === ListingStatus::Published && ($this->expirationDate === null || $evidence->occurredAt < $this->expirationDate->value)) {
            throw ListingViolation::expirationNotReached();
        }
        $this->apply(ListingStatus::Expired, $id, $evidence, $policy, $property, ListingExpired::class);
    }

    public function withdraw(ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): void
    {
        $this->apply(ListingStatus::Withdrawn, $id, $evidence, $policy, $property, ListingWithdrawn::class);
    }

    public function reject(ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): void
    {
        $this->apply(ListingStatus::Rejected, $id, $evidence, $policy, $property, ListingRejected::class);
    }

    public function archive(ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): void
    {
        $this->apply(ListingStatus::Archived, $id, $evidence, $policy, $property, ListingArchived::class);
    }

    public function id(): ListingId
    {
        return $this->id;
    }

    public function propertyId(): PropertyId
    {
        return $this->propertyId;
    }

    public function status(): ListingStatus
    {
        return $this->status;
    }

    public function expirationDate(): ?ExpirationDate
    {
        return $this->expirationDate;
    }

    public function version(): int
    {
        return $this->version;
    }

    /** @return list<ListingRevision> */
    public function revisions(): array
    {
        return $this->revisions;
    }

    /** @return list<ListingEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    /** @param class-string<ListingEvent> $eventClass */
    private function apply(ListingStatus $target, ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property, string $eventClass): void
    {
        $previous = $this->transitionTo($target, $id, $evidence, $policy, $property);
        $this->recordEvent(new $eventClass($this->id, $id, $previous, $evidence));
    }

    private function transitionTo(ListingStatus $target, ListingRevisionId $id, TransitionEvidence $evidence, ListingTransitionPolicy $policy, PropertyAvailability $property): ListingStatus
    {
        $this->guardTime($evidence->occurredAt);
        $policy->assertAllowed($this->status, $target, $evidence, $property);
        $previous = $this->status;
        $this->appendRevision($id, $previous, $target, $evidence);
        $this->status = $target;
        $this->lastChangedAt = $evidence->occurredAt;
        $this->version++;

        return $previous;
    }

    private function appendRevision(ListingRevisionId $id, ?ListingStatus $previous, ListingStatus $status, TransitionEvidence $evidence): void
    {
        foreach ($this->revisions as $revision) {
            if ($revision->id->equals($id)) {
                throw ListingViolation::duplicateRevision();
            }
        }
        $this->revisions[] = new ListingRevision($id, $previous, $status, $evidence->actorId, $evidence->trigger, $evidence->reason, $evidence->origin, $evidence->occurredAt);
    }

    private function guardTime(\DateTimeImmutable $at): void
    {
        if ($at < $this->lastChangedAt) {
            throw InvalidListingValue::field('event_time');
        }
    }

    private function recordEvent(ListingEvent $event, ?int $resultVersion = null): void
    {
        if ($event instanceof AbstractListingEvent) {
            $event->stamp($resultVersion ?? $this->version, count($this->events) + 1);
        }
        $this->events[] = $event;
    }
}
