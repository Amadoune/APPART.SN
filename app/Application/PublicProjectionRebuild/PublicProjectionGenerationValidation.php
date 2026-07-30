<?php

namespace App\Application\PublicProjectionRebuild;

use App\Application\PublicProjectionStore\PublicProjectionGenerationId;

final readonly class PublicProjectionGenerationValidation
{
    /**
     * @param  list<string>  $missingListingIds
     * @param  list<string>  $divergentListingIds
     * @param  list<string>  $corruptListingIds
     */
    public function __construct(
        public PublicProjectionGenerationId $generationId,
        public int $expected,
        public int $observed,
        public array $missingListingIds,
        public array $divergentListingIds,
        public array $corruptListingIds,
    ) {}

    public function isValid(): bool
    {
        return $this->expected === $this->observed && $this->missingListingIds === [] && $this->divergentListingIds === [] && $this->corruptListingIds === [];
    }

    public function progressPercent(): int
    {
        return intdiv(min($this->observed, $this->expected) * 100, $this->expected);
    }

    public function lag(): int
    {
        return max(0, $this->expected - $this->observed);
    }

    public function divergenceCount(): int
    {
        return count($this->divergentListingIds) + count($this->corruptListingIds);
    }
}
