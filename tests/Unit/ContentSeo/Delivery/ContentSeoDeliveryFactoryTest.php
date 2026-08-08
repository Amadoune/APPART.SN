<?php

namespace Tests\Unit\ContentSeo\Delivery;

use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryFactory;
use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryStatus;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryFactory;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryStatus;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventPayload;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventStatus;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventType;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventV1;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventPayload;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventStatus;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventType;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentSeoDeliveryFactoryTest extends TestCase
{
    #[DataProvider('editorialStatuses')]
    public function test_each_editorial_event_produces_exactly_one_delivery(EditorialContentEventStatus $status): void
    {
        $event = new EditorialContentEventV1(EditorialContentEventType::Observed, new EditorialContentEventPayload($status, '2026-08-02T12:00:00.123456Z'));
        $result = (new EditorialContentDeliveryFactory)->create($event);

        self::assertSame(EditorialContentDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['type' => $event->type->value, 'status' => $status->value, 'observedAt' => '2026-08-02T12:00:00.123456Z'], $result->delivery->payload->canonical());
    }

    /** @return iterable<string, array{EditorialContentEventStatus}> */
    public static function editorialStatuses(): iterable
    {
        foreach (EditorialContentEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    #[DataProvider('operationalStatuses')]
    public function test_each_operational_event_produces_exactly_one_delivery(OperationalSeoEventStatus $status): void
    {
        $event = new OperationalSeoEventV1(OperationalSeoEventType::Observed, new OperationalSeoEventPayload($status, '2026-08-02T12:00:00.123456Z'));
        $result = (new OperationalSeoDeliveryFactory)->create($event);

        self::assertSame(OperationalSeoDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['type' => $event->type->value, 'status' => $status->value, 'observedAt' => '2026-08-02T12:00:00.123456Z'], $result->delivery->payload->canonical());
    }

    /** @return iterable<string, array{OperationalSeoEventStatus}> */
    public static function operationalStatuses(): iterable
    {
        foreach (OperationalSeoEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }
}
