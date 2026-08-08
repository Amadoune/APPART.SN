<?php

namespace Tests\Unit\LegacyMigration\Event;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventFactory;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventFactory;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventFactory;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventFactory;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventFactory;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventStatus;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventType;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationCutoverReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationInventoryReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationQuarantineReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationReconciliationReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationWaveReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyMigrationEventFactoryTest extends TestCase
{
    #[DataProvider('inventoryCases')]
    public function test_inventory_result_produces_one_homonymous_event(LegacyMigrationInventoryStatusV1 $public, LegacyMigrationInventoryEventStatus $eventStatus): void
    {
        $reader = $this->createMock(LegacyMigrationInventoryReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new LegacyMigrationInventoryResultV1($public, self::resultObservedAt()));
        $event = (new LegacyMigrationInventoryEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(LegacyMigrationInventoryEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-04T12:00:01.654321Z'], $event->payload->canonical());
    }

    #[DataProvider('waveCases')]
    public function test_wave_result_produces_one_homonymous_event(LegacyMigrationWaveStatusV1 $public, LegacyMigrationWaveEventStatus $eventStatus): void
    {
        $reader = $this->createMock(LegacyMigrationWaveReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new LegacyMigrationWaveResultV1($public, self::resultObservedAt()));
        $event = (new LegacyMigrationWaveEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(LegacyMigrationWaveEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-04T12:00:01.654321Z'], $event->payload->canonical());
    }

    #[DataProvider('reconciliationCases')]
    public function test_reconciliation_result_produces_one_homonymous_event(LegacyMigrationReconciliationStatusV1 $public, LegacyMigrationReconciliationEventStatus $eventStatus): void
    {
        $reader = $this->createMock(LegacyMigrationReconciliationReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new LegacyMigrationReconciliationResultV1($public, self::resultObservedAt()));
        $event = (new LegacyMigrationReconciliationEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(LegacyMigrationReconciliationEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-04T12:00:01.654321Z'], $event->payload->canonical());
    }

    #[DataProvider('quarantineCases')]
    public function test_quarantine_result_produces_one_homonymous_event(LegacyMigrationQuarantineStatusV1 $public, LegacyMigrationQuarantineEventStatus $eventStatus): void
    {
        $reader = $this->createMock(LegacyMigrationQuarantineReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new LegacyMigrationQuarantineResultV1($public, self::resultObservedAt()));
        $event = (new LegacyMigrationQuarantineEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(LegacyMigrationQuarantineEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-04T12:00:01.654321Z'], $event->payload->canonical());
    }

    #[DataProvider('cutoverCases')]
    public function test_cutover_result_produces_one_homonymous_event(LegacyMigrationCutoverStatusV1 $public, LegacyMigrationCutoverEventStatus $eventStatus): void
    {
        $reader = $this->createMock(LegacyMigrationCutoverReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new LegacyMigrationCutoverResultV1($public, self::resultObservedAt()));
        $event = (new LegacyMigrationCutoverEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(LegacyMigrationCutoverEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-04T12:00:01.654321Z'], $event->payload->canonical());
    }

    /** @return iterable<string, array{LegacyMigrationInventoryStatusV1, LegacyMigrationInventoryEventStatus}> */
    public static function inventoryCases(): iterable
    {
        foreach (LegacyMigrationInventoryStatusV1::cases() as $status) {
            yield $status->value => [$status, LegacyMigrationInventoryEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{LegacyMigrationWaveStatusV1, LegacyMigrationWaveEventStatus}> */
    public static function waveCases(): iterable
    {
        foreach (LegacyMigrationWaveStatusV1::cases() as $status) {
            yield $status->value => [$status, LegacyMigrationWaveEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{LegacyMigrationReconciliationStatusV1, LegacyMigrationReconciliationEventStatus}> */
    public static function reconciliationCases(): iterable
    {
        foreach (LegacyMigrationReconciliationStatusV1::cases() as $status) {
            yield $status->value => [$status, LegacyMigrationReconciliationEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{LegacyMigrationQuarantineStatusV1, LegacyMigrationQuarantineEventStatus}> */
    public static function quarantineCases(): iterable
    {
        foreach (LegacyMigrationQuarantineStatusV1::cases() as $status) {
            yield $status->value => [$status, LegacyMigrationQuarantineEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{LegacyMigrationCutoverStatusV1, LegacyMigrationCutoverEventStatus}> */
    public static function cutoverCases(): iterable
    {
        foreach (LegacyMigrationCutoverStatusV1::cases() as $status) {
            yield $status->value => [$status, LegacyMigrationCutoverEventStatus::from($status->value)];
        }
    }

    private static function subject(): LegacyMigrationSubjectKey
    {
        return new LegacyMigrationSubjectKey('legacy-migration:42');
    }

    private static function requestedObservedAt(): LegacyMigrationObservedAt
    {
        return new LegacyMigrationObservedAt(new DateTimeImmutable('2026-08-04T12:00:00.123456Z'));
    }

    private static function resultObservedAt(): LegacyMigrationObservedAt
    {
        return new LegacyMigrationObservedAt(new DateTimeImmutable('2026-08-04T12:00:01.654321Z'));
    }
}
