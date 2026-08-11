<?php

namespace Tests\Unit\PublicSearchResults;

use App\Application\PublicSearchResults\PublicSearchListingSummary;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsResult;
use App\Application\PublicSearchResults\PublicSearchResultsStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PublicSearchResultsContractsTest extends TestCase
{
    public function test_available_result_preserves_public_summary_and_cursor(): void
    {
        $summary = new PublicSearchListingSummary('annonces/a', 'listing-a', 'Titre public', 'Appartement', 'https://media.test/a.webp');
        $result = PublicSearchResultsResult::available([$summary], 'annonces/a');

        self::assertSame(PublicSearchResultsStatus::Available, $result->status);
        self::assertSame([$summary], $result->items);
        self::assertSame('annonces/a', $result->nextCursor);
    }

    public function test_closed_non_available_results_expose_no_items(): void
    {
        self::assertSame(PublicSearchResultsStatus::Empty, PublicSearchResultsResult::empty()->status);
        self::assertSame(PublicSearchResultsStatus::Corrupted, PublicSearchResultsResult::corrupted()->status);
        self::assertSame(PublicSearchResultsStatus::DependencyUnavailable, PublicSearchResultsResult::dependencyUnavailable()->status);
        self::assertSame([], PublicSearchResultsResult::empty()->items);
    }

    public function test_query_enforces_bounded_page_size(): void
    {
        self::assertSame(24, (new PublicSearchResultsQuery(24))->limit);

        $this->expectException(InvalidArgumentException::class);
        new PublicSearchResultsQuery(25);
    }
}
