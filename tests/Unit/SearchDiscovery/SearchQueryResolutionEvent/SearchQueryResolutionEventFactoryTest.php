<?php

namespace Tests\Unit\SearchDiscovery\SearchQueryResolutionEvent;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionResultV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent\SearchQueryResolutionEventFactory;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent\SearchQueryResolutionEventStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent\SearchQueryResolutionEventType;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReaderV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SearchQueryResolutionEventFactoryTest extends TestCase
{
    #[DataProvider('statuses')]
    public function test_factory_reflects_each_public_decision_exactly_once(
        SearchQueryResolutionResultV1 $result,
        SearchQueryResolutionEventStatus $expected,
    ): void {
        $query = new SearchQuery('private query');
        $observedAt = new SearchQueryResolutionObservedAt(new DateTimeImmutable('2026-08-02T14:00:00.123456+00:00'));
        $reader = new class($result, $query, $observedAt) implements PublicSearchQueryResolutionReaderV1
        {
            public int $calls = 0;

            public function __construct(
                private SearchQueryResolutionResultV1 $result,
                private SearchQuery $expectedQuery,
                private SearchQueryResolutionObservedAt $expectedObservedAt,
            ) {}

            public function read(SearchQuery $query, SearchQueryResolutionObservedAt $observedAt): SearchQueryResolutionResultV1
            {
                $this->calls++;
                TestCase::assertSame($this->expectedQuery, $query);
                TestCase::assertSame($this->expectedObservedAt, $observedAt);

                return $this->result;
            }
        };

        $event = (new SearchQueryResolutionEventFactory($reader))->create($query, $observedAt);

        self::assertSame(1, $reader->calls);
        self::assertSame(SearchQueryResolutionEventType::ResolutionObserved, $event->type);
        self::assertSame($expected, $event->payload->status);
        self::assertSame(['status' => $expected->value, 'observedAt' => '2026-08-02T14:00:00.123456Z'], $event->payload->canonical());
        self::assertStringNotContainsString($query->canonical(), json_encode($event->payload->canonical(), JSON_THROW_ON_ERROR));
    }

    public static function statuses(): iterable
    {
        yield [SearchQueryResolutionResultV1::found(), SearchQueryResolutionEventStatus::Found];
        yield [SearchQueryResolutionResultV1::empty(), SearchQueryResolutionEventStatus::Empty];
        yield [SearchQueryResolutionResultV1::corrupted(), SearchQueryResolutionEventStatus::Corrupted];
        yield [SearchQueryResolutionResultV1::dependencyUnavailable(), SearchQueryResolutionEventStatus::DependencyUnavailable];
    }
}
