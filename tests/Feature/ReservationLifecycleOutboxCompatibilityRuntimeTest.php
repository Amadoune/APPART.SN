<?php

namespace Tests\Feature;

use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\ReservationLifecycleEventConsumer\ReservationLifecycleDeliveryConsumer;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventType;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ReservationLifecycleOutboxCompatibilityRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_registry_contains_forty_one_unique_pairs_and_eleven_reservation_consumers(): void
    {
        $registry = $this->app->make(PublicProjectionDeliveryConsumerRegistry::class);
        $registrations = new \ReflectionProperty($registry, 'registrations')->getValue($registry);

        self::assertIsArray($registrations);
        self::assertCount(54, $registrations);
        $pairs = array_map(static fn ($registration): string => $registration->eventType->value.'@'.$registration->payloadVersion->value, $registrations);
        self::assertCount(54, array_unique($pairs));
        foreach (ReservationLifecycleEventType::cases() as $eventType) {
            $matching = array_values(array_filter($registrations, static fn ($registration): bool => $registration->eventType->value === $eventType->value));
            self::assertCount(1, $matching, $eventType->value);
            self::assertInstanceOf(ReservationLifecycleDeliveryConsumer::class, $matching[0]->consumer);
        }
    }

    public function test_runtime_health_remains_healthy_with_the_same_twenty_seven_capabilities(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }
}
