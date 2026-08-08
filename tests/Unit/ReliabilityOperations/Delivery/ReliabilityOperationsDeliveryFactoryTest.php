<?php

namespace Tests\Unit\ReliabilityOperations\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Delivery\AlertingDeliveryFactory;
use Appart\Modules\ReliabilityOperations\Application\Delivery\AlertingDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\CapacityPlanningDeliveryFactory;
use Appart\Modules\ReliabilityOperations\Application\Delivery\CapacityPlanningDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ContinuityDeliveryFactory;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ContinuityDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\MaintenanceOperationsDeliveryFactory;
use Appart\Modules\ReliabilityOperations\Application\Delivery\MaintenanceOperationsDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryFactory;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\OperationalReadinessDeliveryFactory;
use Appart\Modules\ReliabilityOperations\Application\Delivery\OperationalReadinessDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ServiceHealthDeliveryFactory;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ServiceHealthDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventPayload;
use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventV1;
use Appart\Modules\ReliabilityOperations\Application\Event\CapacityPlanningEventPayload;
use Appart\Modules\ReliabilityOperations\Application\Event\CapacityPlanningEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\CapacityPlanningEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\CapacityPlanningEventV1;
use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventPayload;
use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventV1;
use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventPayload;
use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventV1;
use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventPayload;
use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventV1;
use Appart\Modules\ReliabilityOperations\Application\Event\OperationalReadinessEventPayload;
use Appart\Modules\ReliabilityOperations\Application\Event\OperationalReadinessEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\OperationalReadinessEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\OperationalReadinessEventV1;
use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventPayload;
use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventStatus;
use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReliabilityOperationsDeliveryFactoryTest extends TestCase
{
    #[DataProvider('observabilityStatuses')]
    public function test_observability_event_produces_exactly_one_delivery(ObservabilityEventStatus $status): void
    {
        $event = new ObservabilityEventV1(ObservabilityEventType::Observed, new ObservabilityEventPayload($status, self::observedAt()));
        $result = (new ObservabilityDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(ObservabilityDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('service_healthStatuses')]
    public function test_service_health_event_produces_exactly_one_delivery(ServiceHealthEventStatus $status): void
    {
        $event = new ServiceHealthEventV1(ServiceHealthEventType::Observed, new ServiceHealthEventPayload($status, self::observedAt()));
        $result = (new ServiceHealthDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(ServiceHealthDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('alertingStatuses')]
    public function test_alerting_event_produces_exactly_one_delivery(AlertingEventStatus $status): void
    {
        $event = new AlertingEventV1(AlertingEventType::Observed, new AlertingEventPayload($status, self::observedAt()));
        $result = (new AlertingDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(AlertingDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('maintenance_operationsStatuses')]
    public function test_maintenance_operations_event_produces_exactly_one_delivery(MaintenanceOperationsEventStatus $status): void
    {
        $event = new MaintenanceOperationsEventV1(MaintenanceOperationsEventType::Observed, new MaintenanceOperationsEventPayload($status, self::observedAt()));
        $result = (new MaintenanceOperationsDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(MaintenanceOperationsDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('continuityStatuses')]
    public function test_continuity_event_produces_exactly_one_delivery(ContinuityEventStatus $status): void
    {
        $event = new ContinuityEventV1(ContinuityEventType::Observed, new ContinuityEventPayload($status, self::observedAt()));
        $result = (new ContinuityDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(ContinuityDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('capacity_planningStatuses')]
    public function test_capacity_planning_event_produces_exactly_one_delivery(CapacityPlanningEventStatus $status): void
    {
        $event = new CapacityPlanningEventV1(CapacityPlanningEventType::Observed, new CapacityPlanningEventPayload($status, self::observedAt()));
        $result = (new CapacityPlanningDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(CapacityPlanningDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('operational_readinessStatuses')]
    public function test_operational_readiness_event_produces_exactly_one_delivery(OperationalReadinessEventStatus $status): void
    {
        $event = new OperationalReadinessEventV1(OperationalReadinessEventType::Observed, new OperationalReadinessEventPayload($status, self::observedAt()));
        $result = (new OperationalReadinessDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(OperationalReadinessDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    /** @return iterable<string, array{ObservabilityEventStatus}> */
    public static function observabilityStatuses(): iterable
    {
        foreach (ObservabilityEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{ServiceHealthEventStatus}> */
    public static function service_healthStatuses(): iterable
    {
        foreach (ServiceHealthEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{AlertingEventStatus}> */
    public static function alertingStatuses(): iterable
    {
        foreach (AlertingEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{MaintenanceOperationsEventStatus}> */
    public static function maintenance_operationsStatuses(): iterable
    {
        foreach (MaintenanceOperationsEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{ContinuityEventStatus}> */
    public static function continuityStatuses(): iterable
    {
        foreach (ContinuityEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{CapacityPlanningEventStatus}> */
    public static function capacity_planningStatuses(): iterable
    {
        foreach (CapacityPlanningEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{OperationalReadinessEventStatus}> */
    public static function operational_readinessStatuses(): iterable
    {
        foreach (OperationalReadinessEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    private static function observedAt(): string
    {
        return '2026-08-08T12:00:00.123456Z';
    }
}
