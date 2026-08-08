<?php

namespace Tests\Unit\ReliabilityOperations\Event;

use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventFactory;
use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\CapacityPlanningEventFactory;
use Appart\Modules\ReliabilityOperations\Application\Event\CapacityPlanningEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\CapacityPlanningEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventFactory;
use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventFactory;
use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventFactory;
use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\OperationalReadinessEventFactory;
use Appart\Modules\ReliabilityOperations\Application\Event\OperationalReadinessEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\OperationalReadinessEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventFactory;
use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventType;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\AlertingReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\CapacityPlanningReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ContinuityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\MaintenanceOperationsReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ObservabilityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\OperationalReadinessReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ServiceHealthReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ReliabilityOperationsEventFactoryTest extends TestCase
{
    public function test_observability_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (ObservabilityStatusV1::cases() as $status) {
            $reader = $this->createMock(ObservabilityReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new ObservabilityResultV1($status, self::resultObservedAt()));
            $event = (new ObservabilityEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(ObservabilityEventType::Observed, $event->type);
            self::assertSame(ObservabilityEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_service_health_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (ServiceHealthStatusV1::cases() as $status) {
            $reader = $this->createMock(ServiceHealthReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new ServiceHealthResultV1($status, self::resultObservedAt()));
            $event = (new ServiceHealthEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(ServiceHealthEventType::Observed, $event->type);
            self::assertSame(ServiceHealthEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_alerting_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (AlertingStatusV1::cases() as $status) {
            $reader = $this->createMock(AlertingReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new AlertingResultV1($status, self::resultObservedAt()));
            $event = (new AlertingEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(AlertingEventType::Observed, $event->type);
            self::assertSame(AlertingEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_maintenance_operations_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (MaintenanceOperationsStatusV1::cases() as $status) {
            $reader = $this->createMock(MaintenanceOperationsReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new MaintenanceOperationsResultV1($status, self::resultObservedAt()));
            $event = (new MaintenanceOperationsEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(MaintenanceOperationsEventType::Observed, $event->type);
            self::assertSame(MaintenanceOperationsEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_continuity_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (ContinuityStatusV1::cases() as $status) {
            $reader = $this->createMock(ContinuityReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new ContinuityResultV1($status, self::resultObservedAt()));
            $event = (new ContinuityEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(ContinuityEventType::Observed, $event->type);
            self::assertSame(ContinuityEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_capacity_planning_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (CapacityPlanningStatusV1::cases() as $status) {
            $reader = $this->createMock(CapacityPlanningReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new CapacityPlanningResultV1($status, self::resultObservedAt()));
            $event = (new CapacityPlanningEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(CapacityPlanningEventType::Observed, $event->type);
            self::assertSame(CapacityPlanningEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_operational_readiness_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (OperationalReadinessStatusV1::cases() as $status) {
            $reader = $this->createMock(OperationalReadinessReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new OperationalReadinessResultV1($status, self::resultObservedAt()));
            $event = (new OperationalReadinessEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(OperationalReadinessEventType::Observed, $event->type);
            self::assertSame(OperationalReadinessEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    private static function requestedObservedAt(): ReliabilityOperationsObservedAt
    {
        return new ReliabilityOperationsObservedAt(new DateTimeImmutable('2026-08-08T12:00:00.123456Z'));
    }

    private static function resultObservedAt(): ReliabilityOperationsObservedAt
    {
        return new ReliabilityOperationsObservedAt(new DateTimeImmutable('2026-08-08T12:00:01.654321Z'));
    }
}
