<?php

namespace Tests\Unit\Contracts\PublicProjectionStore\Support;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicListingProjectionState;
use App\Application\PublicProjectionStore\PublicProjectionGeneration;
use App\Application\PublicProjectionStore\PublicProjectionGenerationState;
use App\Application\PublicProjectionStore\PublicProjectionWatermarkRelation;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\ReadModels\PublicListingReadModel;
use InvalidArgumentException;

final class FakePublicListingProjectionStore implements PublicListingProjectionWriter, PublicListingQuery
{
    /** @var array<string, PublicProjectionGenerationState> */
    private array $generations = [];

    /** @var array<string, array<string, PublicListingProjectionRecord>> */
    private array $records = [];

    public function __construct(PublicProjectionGeneration $active, ?PublicProjectionGeneration $candidate = null)
    {
        if ($active->state !== PublicProjectionGenerationState::Active || $candidate?->state === PublicProjectionGenerationState::Active) {
            throw new InvalidArgumentException('The fake requires exactly one declared active generation.');
        }

        $this->generations[$active->id->value] = $active->state;
        if ($candidate !== null) {
            $this->generations[$candidate->id->value] = $candidate->state;
        }
    }

    public function applyCurrent(PublicListingProjectionRecord $snapshot): PublicProjectionWriteResult
    {
        $this->requireState($snapshot, PublicListingProjectionState::Current);

        if ($this->generationState($snapshot) !== PublicProjectionGenerationState::Active) {
            return PublicProjectionWriteResult::GenerationMismatch;
        }

        return $this->apply($snapshot);
    }

    public function replaceCanonical(string $previousCanonicalPath, PublicListingProjectionRecord $replacement): PublicProjectionWriteResult
    {
        $this->requireState($replacement, PublicListingProjectionState::Current);
        if ($this->generationState($replacement) !== PublicProjectionGenerationState::Active) {
            return PublicProjectionWriteResult::GenerationMismatch;
        }
        if ($replacement->watermark->compareTo($replacement->watermark) === PublicProjectionWatermarkRelation::Incomplete) {
            return PublicProjectionWriteResult::IncompleteWatermark;
        }

        $generation = $replacement->generationId->value;
        $previous = $this->records[$generation][$previousCanonicalPath] ?? null;
        $existingReplacement = $this->records[$generation][$replacement->canonicalPath] ?? null;

        if ($previous?->state === PublicListingProjectionState::Historical && $existingReplacement?->state === PublicListingProjectionState::Current && $existingReplacement->listingId === $replacement->listingId && $existingReplacement->equivalentPayloadTo($replacement)) {
            return PublicProjectionWriteResult::AlreadyApplied;
        }
        if ($previous === null || $previous->state !== PublicListingProjectionState::Current || $previous->listingId !== $replacement->listingId) {
            return PublicProjectionWriteResult::HistoricalReservationConflict;
        }

        $relation = $replacement->watermark->compareTo($previous->watermark);
        if ($relation === PublicProjectionWatermarkRelation::Older) {
            return PublicProjectionWriteResult::RejectedObsolete;
        }
        if ($relation === PublicProjectionWatermarkRelation::Equal || $relation === PublicProjectionWatermarkRelation::Incomparable) {
            return PublicProjectionWriteResult::DivergentWatermark;
        }

        $availability = $this->canonicalAvailability($replacement);
        if ($availability !== null && $replacement->canonicalPath !== $previousCanonicalPath) {
            return $availability;
        }

        $this->records[$generation][$previousCanonicalPath] = PublicListingProjectionRecord::historical(
            $replacement->listingId,
            $previousCanonicalPath,
            $replacement->watermark,
            $replacement->generationId,
        );
        $this->records[$generation][$replacement->canonicalPath] = clone $replacement;

        return PublicProjectionWriteResult::Applied;
    }

    public function applyTombstone(PublicListingProjectionRecord $tombstone): PublicProjectionWriteResult
    {
        $this->requireState($tombstone, PublicListingProjectionState::Tombstone);
        if ($this->generationState($tombstone) !== PublicProjectionGenerationState::Active) {
            return PublicProjectionWriteResult::GenerationMismatch;
        }

        return $this->apply($tombstone);
    }

    public function writeCandidate(PublicListingProjectionRecord $snapshot): PublicProjectionWriteResult
    {
        $this->requireState($snapshot, PublicListingProjectionState::Current);
        if ($this->generationState($snapshot) !== PublicProjectionGenerationState::Candidate) {
            return PublicProjectionWriteResult::GenerationMismatch;
        }

        return $this->apply($snapshot);
    }

    public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel
    {
        $active = array_search(PublicProjectionGenerationState::Active, $this->generations, true);
        if (! is_string($active)) {
            return null;
        }

        $snapshot = $this->records[$active][$canonicalPath] ?? null;
        if ($snapshot?->state !== PublicListingProjectionState::Current || $snapshot->readModel === null) {
            return null;
        }

        return clone $snapshot->readModel;
    }

    public function snapshot(string $generationId, string $canonicalPath): ?PublicListingProjectionRecord
    {
        $snapshot = $this->records[$generationId][$canonicalPath] ?? null;

        return $snapshot === null ? null : clone $snapshot;
    }

    private function apply(PublicListingProjectionRecord $snapshot): PublicProjectionWriteResult
    {
        if ($snapshot->watermark->compareTo($snapshot->watermark) === PublicProjectionWatermarkRelation::Incomplete) {
            return PublicProjectionWriteResult::IncompleteWatermark;
        }

        $existing = $this->recordForListing($snapshot->generationId->value, $snapshot->listingId);
        if ($existing !== null) {
            $relation = $snapshot->watermark->compareTo($existing->watermark);
            if ($relation === PublicProjectionWatermarkRelation::Equal) {
                return $snapshot->equivalentPayloadTo($existing)
                    ? PublicProjectionWriteResult::AlreadyApplied
                    : PublicProjectionWriteResult::DivergentWatermark;
            }
            if ($relation === PublicProjectionWatermarkRelation::Older) {
                return PublicProjectionWriteResult::RejectedObsolete;
            }
            if ($relation === PublicProjectionWatermarkRelation::Incomparable) {
                return PublicProjectionWriteResult::DivergentWatermark;
            }
            if ($existing->canonicalPath !== $snapshot->canonicalPath) {
                return PublicProjectionWriteResult::CanonicalReplacementRequired;
            }
        }

        $availability = $this->canonicalAvailability($snapshot);
        if ($availability !== null) {
            return $availability;
        }

        $this->records[$snapshot->generationId->value][$snapshot->canonicalPath] = clone $snapshot;

        return PublicProjectionWriteResult::Applied;
    }

    private function canonicalAvailability(PublicListingProjectionRecord $candidate): ?PublicProjectionWriteResult
    {
        $existing = $this->records[$candidate->generationId->value][$candidate->canonicalPath] ?? null;
        if ($existing === null || $existing->listingId === $candidate->listingId) {
            return null;
        }

        return $existing->state === PublicListingProjectionState::Current
            ? PublicProjectionWriteResult::CanonicalCollision
            : PublicProjectionWriteResult::HistoricalReservationConflict;
    }

    private function recordForListing(string $generationId, string $listingId): ?PublicListingProjectionRecord
    {
        foreach ($this->records[$generationId] ?? [] as $snapshot) {
            if ($snapshot->listingId === $listingId && $snapshot->state !== PublicListingProjectionState::Historical) {
                return $snapshot;
            }
        }

        return null;
    }

    private function generationState(PublicListingProjectionRecord $snapshot): ?PublicProjectionGenerationState
    {
        return $this->generations[$snapshot->generationId->value] ?? null;
    }

    private function requireState(PublicListingProjectionRecord $snapshot, PublicListingProjectionState $expected): void
    {
        if ($snapshot->state !== $expected) {
            throw new InvalidArgumentException('Public projection write intent does not match snapshot state.');
        }
    }
}
