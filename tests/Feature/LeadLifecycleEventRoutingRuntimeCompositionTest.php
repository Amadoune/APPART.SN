<?php

namespace Tests\Feature;

use App\Application\LeadLifecycleEventConsumption\LeadLifecycleDeliveryConsumptionPolicy;
use App\Application\LeadLifecycleEventRouting\DurableLeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventRouting\LeadLifecycleInboxStore;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportSerializer;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\LeadLifecycleEventRouting\PostgreSql\PostgreSqlLeadLifecycleInboxRepository;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class LeadLifecycleEventRoutingRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_production_graph_is_unique_lazy_and_fully_resolved(): void
    {
        foreach ([LeadLifecycleTransportSerializer::class, PostgreSqlLeadLifecycleInboxRepository::class, LeadLifecycleInboxStore::class, DurableLeadLifecycleEventRouter::class, LeadLifecycleEventRouter::class, LeadLifecycleDeliveryConsumptionPolicy::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $serializer = $this->app->make(LeadLifecycleTransportSerializer::class);
        $repository = $this->app->make(PostgreSqlLeadLifecycleInboxRepository::class);
        $store = $this->app->make(LeadLifecycleInboxStore::class);
        $implementation = $this->app->make(DurableLeadLifecycleEventRouter::class);
        $router = $this->app->make(LeadLifecycleEventRouter::class);

        self::assertSame($repository, $store);
        self::assertSame($implementation, $router);
        self::assertSame($store, (new ReflectionProperty($implementation, 'store'))->getValue($implementation));
        self::assertSame($this->app->make(PDO::class), (new ReflectionProperty($repository, 'connection'))->getValue($repository));
        self::assertSame($serializer, (new ReflectionProperty($repository, 'serializer'))->getValue($repository));
    }

    public function test_runtime_health_is_explicit_structural_and_healthy(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
    }
}
