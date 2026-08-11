<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence;

use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\ListingRevision;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;
use Throwable;

final class ListingMapper
{
    public function toSnapshot(Listing $listing): ListingSnapshot
    {
        $revisions = array_map(
            fn (ListingRevision $revision, int $index): ListingRevisionSnapshot => new ListingRevisionSnapshot(
                $index + 1,
                $revision->id->value,
                $revision->previousStatus?->value,
                $revision->status->value,
                $revision->actorId->value,
                $revision->trigger->value,
                $revision->reason?->value,
                $revision->origin->value,
                $this->date($revision->occurredAt),
            ),
            $listing->revisions(),
            array_keys($listing->revisions()),
        );
        if ($revisions === []) {
            throw PersistentListingIntegrity::invalid('empty history');
        }

        return new ListingSnapshot(
            $listing->id()->value,
            $listing->propertyId()->value,
            $listing->status()->value,
            $revisions[array_key_last($revisions)]->occurredAt,
            $listing->expirationDate() === null ? null : $this->date($listing->expirationDate()->value),
            $listing->version(),
            $revisions,
        );
    }

    public function toAggregate(ListingSnapshot $snapshot): Listing
    {
        try {
            $status = ListingStatus::tryFrom($snapshot->status) ?? throw PersistentListingIntegrity::invalid('status');
            $revisions = $this->revisions($snapshot->revisions);
            $lastChangedAt = $this->parseDate($snapshot->lastChangedAt);
            $this->assertCoherent($snapshot, $status, $revisions, $lastChangedAt);

            $listing = Listing::reconstitute(
                ListingId::fromString($snapshot->id),
                PropertyId::fromString($snapshot->propertyId),
                $status,
                $lastChangedAt,
                $revisions,
                $snapshot->expirationDate === null ? null : ExpirationDate::fromDateTime($this->parseDate($snapshot->expirationDate)),
                $snapshot->version,
            );
            if ($listing->releaseEvents() !== []) {
                throw PersistentListingIntegrity::invalid('events');
            }

            return $listing;
        } catch (PersistentListingIntegrity $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentListingIntegrity::invalid('snapshot');
        }
    }

    /** @param list<ListingRevisionSnapshot> $snapshots
     * @return list<ListingRevision>
     */
    private function revisions(array $snapshots): array
    {
        $revisions = [];
        $ids = [];
        foreach ($snapshots as $index => $snapshot) {
            if ($snapshot->sequence !== $index + 1 || isset($ids[$snapshot->id])) {
                throw PersistentListingIntegrity::invalid('revision order or identity');
            }
            $ids[$snapshot->id] = true;
            $revisions[] = new ListingRevision(
                ListingRevisionId::fromString($snapshot->id),
                $snapshot->previousStatus === null ? null : (ListingStatus::tryFrom($snapshot->previousStatus) ?? throw PersistentListingIntegrity::invalid('previous status')),
                ListingStatus::tryFrom($snapshot->status) ?? throw PersistentListingIntegrity::invalid('revision status'),
                ActorId::fromString($snapshot->actorId),
                TransitionTrigger::tryFrom($snapshot->trigger) ?? throw PersistentListingIntegrity::invalid('trigger'),
                $snapshot->reason === null ? null : TransitionReason::fromString($snapshot->reason),
                TransitionOrigin::tryFrom($snapshot->origin) ?? throw PersistentListingIntegrity::invalid('origin'),
                $this->parseDate($snapshot->occurredAt),
            );
        }

        return $revisions;
    }

    /** @param list<ListingRevision> $revisions */
    private function assertCoherent(ListingSnapshot $snapshot, ListingStatus $status, array $revisions, DateTimeImmutable $lastChangedAt): void
    {
        if ($snapshot->version < 0 || $revisions === [] || count($revisions) !== $snapshot->version + 1) {
            throw PersistentListingIntegrity::invalid('version or history');
        }
        $policy = new ListingTransitionPolicy;
        foreach ($revisions as $index => $revision) {
            if ($index === 0) {
                if ($revision->previousStatus !== null || $revision->status !== ListingStatus::Draft) {
                    throw PersistentListingIntegrity::invalid('initial revision');
                }
                $policy->assertDraftCreation($this->evidence($revision), PropertyAvailability::Eligible);

                continue;
            }
            $previous = $revisions[$index - 1];
            if ($revision->previousStatus !== $previous->status || $revision->occurredAt < $previous->occurredAt) {
                throw PersistentListingIntegrity::invalid('revision chain');
            }
            $policy->assertAllowed($previous->status, $revision->status, $this->evidence($revision), PropertyAvailability::Eligible);
        }
        $last = $revisions[array_key_last($revisions)];
        if ($last->status !== $status || $last->occurredAt != $lastChangedAt) {
            throw PersistentListingIntegrity::invalid('current state');
        }
        if ($snapshot->expirationDate !== null && $this->parseDate($snapshot->expirationDate) <= $revisions[0]->occurredAt) {
            throw PersistentListingIntegrity::invalid('expiration');
        }
    }

    private function evidence(ListingRevision $revision): TransitionEvidence
    {
        return new TransitionEvidence($revision->actorId, $revision->trigger, $revision->reason, $revision->origin, $revision->occurredAt);
    }

    private function date(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d\TH:i:s.uP');
    }

    private function parseDate(string $date): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s.uP', $date);
        $errors = DateTimeImmutable::getLastErrors();
        if ($parsed === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $this->date($parsed) !== $date) {
            throw PersistentListingIntegrity::invalid('date');
        }

        return $parsed;
    }
}
