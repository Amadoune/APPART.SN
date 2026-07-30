<?php

namespace Tests\Feature;

use App\Application\ReservationLifecycleEventRouting\DeterministicReservationLifecycleEventRouter;
use App\Application\ReservationLifecycleEventRouting\ReservationLifecycleInboxStore;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleEventRouterPort;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportSerializer;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\ReservationLifecycleEventRouting\PostgreSql\PostgreSqlReservationLifecycleInboxRepository;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ReservationLifecycleEventRoutingRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_production_routing_graph_is_lazy_unique_and_fully_resolved(): void
    {
        foreach ([ReservationLifecycleTransportSerializer::class, PostgreSqlReservationLifecycleInboxRepository::class, ReservationLifecycleInboxStore::class, DeterministicReservationLifecycleEventRouter::class, ReservationLifecycleEventRouterPort::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $serializer = $this->app->make(ReservationLifecycleTransportSerializer::class);
        $repository = $this->app->make(PostgreSqlReservationLifecycleInboxRepository::class);
        $store = $this->app->make(ReservationLifecycleInboxStore::class);
        $implementation = $this->app->make(DeterministicReservationLifecycleEventRouter::class);
        $router = $this->app->make(ReservationLifecycleEventRouterPort::class);

        self::assertSame($repository, $store);
        self::assertSame($implementation, $router);
        self::assertSame($store, (new ReflectionProperty($implementation, 'store'))->getValue($implementation));
        self::assertSame($this->app->make(PDO::class), (new ReflectionProperty($repository, 'connection'))->getValue($repository));
        self::assertSame($serializer, (new ReflectionProperty($repository, 'serializer'))->getValue($repository));
        self::assertStringNotContainsStringIgnoringCase('fake', $implementation::class);
        self::assertStringNotContainsStringIgnoringCase('null', $implementation::class);
    }

    public function test_runtime_health_explicitly_inspects_routing_graph_and_remains_healthy(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }
}
