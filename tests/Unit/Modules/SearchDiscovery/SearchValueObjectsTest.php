<?php

namespace Tests\Unit\Modules\SearchDiscovery;

use Appart\Modules\SearchDiscovery\Domain\Exception\InvalidSearchValue;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetKey;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetValue;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchRank;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SearchValueObjectsTest extends TestCase
{
    public function test_identifiers_require_a_uuid(): void
    {
        $this->expectException(InvalidSearchValue::class);
        ListingId::fromString('listing-1');
    }

    #[DataProvider('invalidRanks')]
    public function test_rank_has_explicit_bounds(int $rank): void
    {
        $this->expectException(InvalidSearchValue::class);
        SearchRank::fromInt($rank);
    }

    /** @return iterable<string, array{int}> */
    public static function invalidRanks(): iterable
    {
        yield 'negative' => [-1];
        yield 'above maximum' => [10001];
    }

    public function test_facet_key_is_normalized(): void
    {
        self::assertSame('property_type', SearchFacetKey::fromString('  PROPERTY_TYPE  ')->value);
    }

    public function test_invalid_facet_key_is_rejected(): void
    {
        $this->expectException(InvalidSearchValue::class);
        SearchFacetKey::fromString('property type');
    }

    public function test_facet_value_is_normalized_and_non_empty(): void
    {
        self::assertSame('Dakar Plateau', SearchFacetValue::fromString('  Dakar   Plateau  ')->value);

        $this->expectException(InvalidSearchValue::class);
        SearchFacetValue::fromString('   ');
    }
}
