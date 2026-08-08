<?php

namespace Tests\Unit\SearchDiscovery\SearchQueryResolutionDelivery;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryFactory;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent\SearchQueryResolutionEventPayload;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent\SearchQueryResolutionEventStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent\SearchQueryResolutionEventType;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent\SearchQueryResolutionEventV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SearchQueryResolutionDeliveryFactoryTest extends TestCase
{
    #[DataProvider('statuses')]
    public function test_each_event_produces_exactly_one_unchanged_delivery_status(
        SearchQueryResolutionEventStatus $eventStatus,
        SearchQueryResolutionDeliveryStatus $deliveryStatus,
    ): void {
        $event = new SearchQueryResolutionEventV1(
            SearchQueryResolutionEventType::ResolutionObserved,
            new SearchQueryResolutionEventPayload($eventStatus, '2026-08-02T15:00:00.123456Z'),
        );

        $result = (new SearchQueryResolutionDeliveryFactory)->create($event);

        self::assertSame(SearchQueryResolutionDeliveryV1::TYPE, 'search_query_resolution.delivery.v1');
        self::assertSame($deliveryStatus, $result->status());
        self::assertSame(
            ['status' => $deliveryStatus->value, 'observedAt' => '2026-08-02T15:00:00.123456Z'],
            $result->delivery->payload->canonical(),
        );
        self::assertSame($eventStatus, $event->payload->status);
    }

    public static function statuses(): iterable
    {
        yield [SearchQueryResolutionEventStatus::Found, SearchQueryResolutionDeliveryStatus::Found];
        yield [SearchQueryResolutionEventStatus::Empty, SearchQueryResolutionDeliveryStatus::Empty];
        yield [SearchQueryResolutionEventStatus::Corrupted, SearchQueryResolutionDeliveryStatus::Corrupted];
        yield [SearchQueryResolutionEventStatus::DependencyUnavailable, SearchQueryResolutionDeliveryStatus::DependencyUnavailable];
    }
}
