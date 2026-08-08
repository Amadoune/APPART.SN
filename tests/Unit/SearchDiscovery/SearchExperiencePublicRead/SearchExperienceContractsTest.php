<?php

namespace Tests\Unit\SearchDiscovery\SearchExperiencePublicRead;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\Contract\SearchQueryReaderV1;
use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQueryResultStatusV1;
use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQueryResultV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class SearchExperienceContractsTest extends TestCase
{
    #[Test]
    public function catalogue_is_closed_and_exact(): void
    {
        self::assertSame(
            ['found', 'empty', 'corrupted', 'dependency_unavailable'],
            array_column(SearchQueryResultStatusV1::cases(), 'value'),
        );
    }

    #[Test]
    public function result_contains_only_the_closed_status(): void
    {
        self::assertSame(SearchQueryResultStatusV1::Found, SearchQueryResultV1::found()->status);
        self::assertSame(SearchQueryResultStatusV1::Empty, SearchQueryResultV1::empty()->status);
        self::assertSame(SearchQueryResultStatusV1::Corrupted, SearchQueryResultV1::corrupted()->status);
        self::assertSame(SearchQueryResultStatusV1::DependencyUnavailable, SearchQueryResultV1::dependencyUnavailable()->status);
        self::assertSame(['status'], array_keys(get_object_vars(SearchQueryResultV1::found())));
    }

    #[Test]
    public function query_is_declarative_immutable_and_not_parsed(): void
    {
        $query = new SearchQuery('  Appartement Dakar  ');

        self::assertSame('  Appartement Dakar  ', $query->value);
        self::assertSame($query->value, $query->canonical());
    }

    #[Test]
    public function observation_is_utc_canonical_and_has_no_implicit_clock(): void
    {
        $observedAt = new SearchObservedAt(new DateTimeImmutable('2026-08-01 14:15:16.123456+02:00'));

        self::assertSame('UTC', $observedAt->value->getTimezone()->getName());
        self::assertSame('2026-08-01T12:15:16.123456Z', $observedAt->canonical());
        self::assertFalse(method_exists($observedAt, 'now'));
    }

    #[Test]
    public function reader_signature_is_owner_scoped_and_typed(): void
    {
        $method = new ReflectionMethod(SearchQueryReaderV1::class, 'read');

        self::assertTrue($method->getDeclaringClass()->isInterface());
        self::assertSame(
            [SearchQuery::class, SearchObservedAt::class],
            array_map(static fn ($parameter): string => (string) $parameter->getType(), $method->getParameters()),
        );
        self::assertSame(SearchQueryResultV1::class, (string) $method->getReturnType());
    }
}
