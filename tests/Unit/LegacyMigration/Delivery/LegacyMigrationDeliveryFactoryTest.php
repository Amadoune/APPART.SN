<?php

namespace Tests\Unit\LegacyMigration\Delivery;

use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryFactory;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryFactory;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryFactory;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryFactory;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryFactory;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventPayload;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventV1;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventPayload;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventV1;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventPayload;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventV1;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventPayload;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventV1;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventPayload;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyMigrationDeliveryFactoryTest extends TestCase
{
    #[DataProvider('inventoryStatuses')]
    public function test_inventory_event_produces_one_delivery(LegacyMigrationInventoryEventStatus $status): void
    {
        $event = new LegacyMigrationInventoryEventV1(LegacyMigrationInventoryEventType::Observed, new LegacyMigrationInventoryEventPayload($status, self::observedAt()));
        $result = (new LegacyMigrationInventoryDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(LegacyMigrationInventoryDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
    }

    #[DataProvider('waveStatuses')]
    public function test_wave_event_produces_one_delivery(LegacyMigrationWaveEventStatus $status): void
    {
        $event = new LegacyMigrationWaveEventV1(LegacyMigrationWaveEventType::Observed, new LegacyMigrationWaveEventPayload($status, self::observedAt()));
        $result = (new LegacyMigrationWaveDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(LegacyMigrationWaveDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
    }

    #[DataProvider('reconciliationStatuses')]
    public function test_reconciliation_event_produces_one_delivery(LegacyMigrationReconciliationEventStatus $status): void
    {
        $event = new LegacyMigrationReconciliationEventV1(LegacyMigrationReconciliationEventType::Observed, new LegacyMigrationReconciliationEventPayload($status, self::observedAt()));
        $result = (new LegacyMigrationReconciliationDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(LegacyMigrationReconciliationDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
    }

    #[DataProvider('quarantineStatuses')]
    public function test_quarantine_event_produces_one_delivery(LegacyMigrationQuarantineEventStatus $status): void
    {
        $event = new LegacyMigrationQuarantineEventV1(LegacyMigrationQuarantineEventType::Observed, new LegacyMigrationQuarantineEventPayload($status, self::observedAt()));
        $result = (new LegacyMigrationQuarantineDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(LegacyMigrationQuarantineDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
    }

    #[DataProvider('cutoverStatuses')]
    public function test_cutover_event_produces_one_delivery(LegacyMigrationCutoverEventStatus $status): void
    {
        $event = new LegacyMigrationCutoverEventV1(LegacyMigrationCutoverEventType::Observed, new LegacyMigrationCutoverEventPayload($status, self::observedAt()));
        $result = (new LegacyMigrationCutoverDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(LegacyMigrationCutoverDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
    }

    /** @return iterable<string, array{LegacyMigrationInventoryEventStatus}> */
    public static function inventoryStatuses(): iterable
    {
        foreach (LegacyMigrationInventoryEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{LegacyMigrationWaveEventStatus}> */
    public static function waveStatuses(): iterable
    {
        foreach (LegacyMigrationWaveEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{LegacyMigrationReconciliationEventStatus}> */
    public static function reconciliationStatuses(): iterable
    {
        foreach (LegacyMigrationReconciliationEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{LegacyMigrationQuarantineEventStatus}> */
    public static function quarantineStatuses(): iterable
    {
        foreach (LegacyMigrationQuarantineEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{LegacyMigrationCutoverEventStatus}> */
    public static function cutoverStatuses(): iterable
    {
        foreach (LegacyMigrationCutoverEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    private static function observedAt(): string
    {
        return '2026-08-04T12:00:00.123456Z';
    }
}
