<?php

namespace App\Application\PublicProjectionRebuild;

final readonly class PublicProjectionRebuildReport
{
    /** @param list<string> $rejectedListingIds */
    public function __construct(
        public int $processed,
        public int $applied,
        public int $alreadyApplied,
        public int $missing,
        public array $rejectedListingIds,
        public ?string $nextCheckpoint,
    ) {}

    public function isComplete(): bool
    {
        return $this->nextCheckpoint === null;
    }
}
