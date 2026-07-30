<?php

namespace App\Application\PublicProjectionRebuild;

use App\Application\PublicProjectionRebuild\Contract\PublicProjectionCandidateFactory;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionRebuildEnumerator;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use InvalidArgumentException;

final readonly class PublicProjectionRebuilder
{
    public function __construct(
        private PublicProjectionRebuildEnumerator $enumerator,
        private PublicProjectionCandidateFactory $factory,
        private PublicListingProjectionWriter $writer,
        private int $pageSize,
    ) {
        if ($pageSize < 1) {
            throw new InvalidArgumentException('The rebuild page size must be positive.');
        }
    }

    public function runOnce(PublicProjectionGenerationId $generationId, PublicProjectionRebuildScope $scope, ?string $checkpoint = null): PublicProjectionRebuildReport
    {
        $page = $this->enumerator->page($scope, $checkpoint, $this->pageSize);
        $applied = $already = $missing = 0;
        $rejected = [];

        foreach ($page->listingIds as $listingId) {
            $record = $this->factory->rebuild($listingId, $generationId);
            if ($record === null) {
                $missing++;

                continue;
            }
            if (! $record->generationId->equals($generationId) || $record->listingId !== $listingId) {
                $rejected[] = $listingId;

                continue;
            }
            match ($this->writer->writeCandidate($record)) {
                PublicProjectionWriteResult::Applied => $applied++,
                PublicProjectionWriteResult::AlreadyApplied => $already++,
                PublicProjectionWriteResult::CanonicalCollision,
                PublicProjectionWriteResult::CanonicalReplacementRequired,
                PublicProjectionWriteResult::DivergentWatermark,
                PublicProjectionWriteResult::GenerationMismatch,
                PublicProjectionWriteResult::HistoricalReservationConflict,
                PublicProjectionWriteResult::IncompleteWatermark,
                PublicProjectionWriteResult::RejectedObsolete => $rejected[] = $listingId,
            };
        }

        return new PublicProjectionRebuildReport(count($page->listingIds), $applied, $already, $missing, $rejected, $page->nextCheckpoint);
    }
}
