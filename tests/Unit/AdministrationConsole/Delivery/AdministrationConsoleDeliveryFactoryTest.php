<?php

namespace Tests\Unit\AdministrationConsole\Delivery;

use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryFactory;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryFactory;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryFactory;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventPayload;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventV1;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventPayload;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventV1;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventPayload;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrationConsoleDeliveryFactoryTest extends TestCase
{
    #[DataProvider('operatorStatuses')]
    public function test_each_operator_event_produces_exactly_one_delivery(AdministrationOperatorEventStatus $status): void
    {
        $event = new AdministrationOperatorEventV1(AdministrationOperatorEventType::Observed, new AdministrationOperatorEventPayload($status, self::observedAt()));

        $result = (new AdministrationOperatorDeliveryFactory)->create($event);

        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(AdministrationOperatorDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
    }

    #[DataProvider('queueStatuses')]
    public function test_each_queue_event_produces_exactly_one_delivery(AdministrationQueueEventStatus $status): void
    {
        $event = new AdministrationQueueEventV1(AdministrationQueueEventType::Observed, new AdministrationQueueEventPayload($status, self::observedAt()));

        $result = (new AdministrationQueueDeliveryFactory)->create($event);

        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(AdministrationQueueDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
    }

    #[DataProvider('auditStatuses')]
    public function test_each_audit_event_produces_exactly_one_delivery(AdministrationAuditEventStatus $status): void
    {
        $event = new AdministrationAuditEventV1(AdministrationAuditEventType::Observed, new AdministrationAuditEventPayload($status, self::observedAt()));

        $result = (new AdministrationAuditDeliveryFactory)->create($event);

        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(AdministrationAuditDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
    }

    /** @return iterable<string, array{AdministrationOperatorEventStatus}> */
    public static function operatorStatuses(): iterable
    {
        foreach (AdministrationOperatorEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{AdministrationQueueEventStatus}> */
    public static function queueStatuses(): iterable
    {
        foreach (AdministrationQueueEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{AdministrationAuditEventStatus}> */
    public static function auditStatuses(): iterable
    {
        foreach (AdministrationAuditEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    private static function observedAt(): string
    {
        return '2026-08-03T12:00:00.123456Z';
    }
}
