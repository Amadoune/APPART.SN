<?php

namespace Tests\Unit\SearchDiscovery\SearchQueryResolution;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\Contract\SearchQueryResolutionReaderV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionResultV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class SearchQueryResolutionContractsTest extends TestCase
{
    #[Test]
    public function catalogue_is_closed_and_exact(): void
    {
        self::assertSame(['found', 'empty', 'corrupted', 'dependency_unavailable'], array_column(SearchQueryResolutionStatusV1::cases(), 'value'));
    }

    #[Test]
    public function result_contains_only_the_closed_status(): void
    {
        self::assertSame(SearchQueryResolutionStatusV1::Found, SearchQueryResolutionResultV1::found()->status);
        self::assertSame(SearchQueryResolutionStatusV1::Empty, SearchQueryResolutionResultV1::empty()->status);
        self::assertSame(SearchQueryResolutionStatusV1::Corrupted, SearchQueryResolutionResultV1::corrupted()->status);
        self::assertSame(SearchQueryResolutionStatusV1::DependencyUnavailable, SearchQueryResolutionResultV1::dependencyUnavailable()->status);
        self::assertSame(['status'], array_keys(get_object_vars(SearchQueryResolutionResultV1::found())));
    }

    #[Test]
    public function observed_at_is_explicit_utc_canonical_and_has_no_clock(): void
    {
        $instant = new SearchQueryResolutionObservedAt(new DateTimeImmutable('2026-08-01 14:15:16.123456+02:00'));
        self::assertSame('UTC', $instant->value->getTimezone()->getName());
        self::assertSame('2026-08-01T12:15:16.123456Z', $instant->canonical());
        self::assertFalse(method_exists($instant, 'now'));
    }

    #[Test]
    public function reader_signature_reuses_the_opaque_search_query(): void
    {
        $method = new ReflectionMethod(SearchQueryResolutionReaderV1::class, 'read');
        self::assertSame([SearchQuery::class, SearchQueryResolutionObservedAt::class], array_map(static fn ($parameter): string => (string) $parameter->getType(), $method->getParameters()));
        self::assertSame(SearchQueryResolutionResultV1::class, (string) $method->getReturnType());
    }
}
