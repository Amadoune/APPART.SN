<?php

namespace App\Application\PublicSearchResults;

final readonly class PublicSearchResultsResult
{
    /** @param list<PublicSearchListingSummary> $items */
    private function __construct(
        public PublicSearchResultsStatus $status,
        public array $items = [],
        public ?string $nextCursor = null,
    ) {}

    /** @param list<PublicSearchListingSummary> $items */
    public static function available(array $items, ?string $nextCursor): self
    {
        return new self(PublicSearchResultsStatus::Available, $items, $nextCursor);
    }

    public static function empty(): self
    {
        return new self(PublicSearchResultsStatus::Empty);
    }

    public static function corrupted(): self
    {
        return new self(PublicSearchResultsStatus::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(PublicSearchResultsStatus::DependencyUnavailable);
    }
}
