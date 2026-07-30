<?php

namespace Tests\Feature;

use App\Application\AdministrativeActionLifecycleEventConsumption\AdministrativeActionLifecycleDeliveryConsumptionPolicy;
use App\Application\AdministrativeActionLifecycleEventRouting\AdministrativeActionLifecycleInboxStore;
use App\Application\AdministrativeActionLifecycleEventRouting\DurableAdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportSerializer;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\AdministrativeActionLifecycleEventRouting\PostgreSql\PostgreSqlAdministrativeActionLifecycleInboxRepository;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class AdministrativeActionLifecycleEventRoutingRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_production_graph_is_unique_lazy_and_resolved(): void
    {
        foreach ([
            AdministrativeActionLifecycleTransportSerializer::class,
            PostgreSqlAdministrativeActionLifecycleInboxRepository::class,
            AdministrativeActionLifecycleInboxStore::class,
            DurableAdministrativeActionLifecycleEventRouter::class,
            AdministrativeActionLifecycleEventRouter::class,
            AdministrativeActionLifecycleDeliveryConsumptionPolicy::class,
        ] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $serializer = $this->app->make(AdministrativeActionLifecycleTransportSerializer::class);
        $repository = $this->app->make(PostgreSqlAdministrativeActionLifecycleInboxRepository::class);
        $store = $this->app->make(AdministrativeActionLifecycleInboxStore::class);
        $implementation = $this->app->make(DurableAdministrativeActionLifecycleEventRouter::class);
        $router = $this->app->make(AdministrativeActionLifecycleEventRouter::class);

        self::assertSame($repository, $store);
        self::assertSame($implementation, $router);
        self::assertSame($store, (new ReflectionProperty($implementation, 'store'))->getValue($implementation));
        self::assertSame($this->app->make(PDO::class), (new ReflectionProperty($repository, 'connection'))->getValue($repository));
        self::assertSame($serializer, (new ReflectionProperty($repository, 'serializer'))->getValue($repository));
    }

    public function test_runtime_health_is_structural_and_healthy_at_fifty(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
    }
}
