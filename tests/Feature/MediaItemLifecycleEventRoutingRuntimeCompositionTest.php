<?php

namespace Tests\Feature;

use App\Application\MediaItemLifecycleEventConsumption\MediaItemLifecycleDeliveryConsumptionPolicy;
use App\Application\MediaItemLifecycleEventRouting\DurableMediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStore;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportSerializer;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\MediaItemLifecycleEventRouting\PostgreSql\PostgreSqlMediaItemLifecycleInboxRepository;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class MediaItemLifecycleEventRoutingRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_production_graph_is_unique_lazy_and_resolved(): void
    {
        foreach ([MediaItemLifecycleTransportSerializer::class, PostgreSqlMediaItemLifecycleInboxRepository::class, MediaItemLifecycleInboxStore::class, DurableMediaItemLifecycleEventRouter::class, MediaItemLifecycleEventRouter::class, MediaItemLifecycleDeliveryConsumptionPolicy::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $serializer = $this->app->make(MediaItemLifecycleTransportSerializer::class);
        $repository = $this->app->make(PostgreSqlMediaItemLifecycleInboxRepository::class);
        $store = $this->app->make(MediaItemLifecycleInboxStore::class);
        $implementation = $this->app->make(DurableMediaItemLifecycleEventRouter::class);
        $router = $this->app->make(MediaItemLifecycleEventRouter::class);

        self::assertSame($repository, $store);
        self::assertSame($implementation, $router);
        self::assertSame($store, (new ReflectionProperty($implementation, 'store'))->getValue($implementation));
        self::assertSame($this->app->make(PDO::class), (new ReflectionProperty($repository, 'connection'))->getValue($repository));
        self::assertSame($serializer, (new ReflectionProperty($repository, 'serializer'))->getValue($repository));
    }

    public function test_runtime_health_is_structural_and_healthy_at_forty_five(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
    }
}
