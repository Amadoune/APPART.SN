<?php

namespace Tests\Unit\SearchDiscovery\SearchQueryResolutionOutbox;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryPayload;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox\SearchQueryResolutionOutboxPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox\SearchQueryResolutionOutboxStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SearchQueryResolutionOutboxPolicyTest extends TestCase
{
    #[DataProvider('statuses')]
    public function test_policy_is_deterministic(SearchQueryResolutionDeliveryStatus $input, SearchQueryResolutionOutboxStatus $expected): void
    {
        $delivery = new SearchQueryResolutionDeliveryV1(new SearchQueryResolutionDeliveryPayload($input, '2026-08-02T16:00:00.123456Z'));
        $policy = new SearchQueryResolutionOutboxPolicy;

        self::assertSame($policy->prepare($delivery)->outboxId, $policy->prepare($delivery)->outboxId);
        self::assertSame($expected, $policy->prepare($delivery)->status);
    }

    public static function statuses(): iterable
    {
        yield [SearchQueryResolutionDeliveryStatus::Found, SearchQueryResolutionOutboxStatus::Found];
        yield [SearchQueryResolutionDeliveryStatus::Empty, SearchQueryResolutionOutboxStatus::Empty];
        yield [SearchQueryResolutionDeliveryStatus::Corrupted, SearchQueryResolutionOutboxStatus::Corrupted];
        yield [SearchQueryResolutionDeliveryStatus::DependencyUnavailable, SearchQueryResolutionOutboxStatus::DependencyUnavailable];
    }
}
