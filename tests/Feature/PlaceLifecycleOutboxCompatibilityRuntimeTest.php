<?php

namespace Tests\Feature;

use App\Application\PlaceLifecycleEventConsumption\PlaceLifecycleDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventType;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PlaceLifecycleOutboxCompatibilityRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_worker_registry_contains_three_unique_place_registrations(): void
    {
        $registry = $this->app->make(PublicProjectionDeliveryConsumerRegistry::class);
        $registrations = (new ReflectionProperty($registry, 'registrations'))->getValue($registry);
        self::assertIsArray($registrations);
        $pairs = array_map(
            static fn ($registration): string => $registration->eventType->value.'@'.$registration->payloadVersion->value,
            $registrations,
        );

        self::assertCount(count($registrations), array_unique($pairs));
        foreach (PlaceLifecycleEventType::cases() as $eventType) {
            $matching = array_values(array_filter(
                $registrations,
                static fn ($registration): bool => $registration->eventType == PublicProjectionDeliveryEventType::fromString($eventType->value)
                    && $registration->payloadVersion == PublicProjectionDeliveryPayloadVersion::fromInt(1),
            ));
            self::assertCount(1, $matching);
            self::assertInstanceOf(PlaceLifecycleDeliveryConsumer::class, $matching[0]->consumer);
        }
    }

    public function test_runtime_health_remains_healthy_with_the_reserved_place_consumer_capacity(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
    }
}
