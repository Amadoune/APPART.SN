<?php

namespace Tests\Feature;

use App\Application\PropertyLifecycleEventRouting\Contract\PropertyLifecycleEventDestination;
use App\Application\PropertyLifecycleEventRouting\DurablePropertyLifecycleEventRouter;
use App\Application\PropertyLifecycleEventTransport\Contract\PropertyLifecycleEventRouter;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\PropertyLifecycleEventRouting\PostgreSql\PostgreSqlPropertyLifecycleEventInbox;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventSerializer;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PropertyLifecycleEventRoutingRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_production_routing_graph_is_lazy_unique_and_fully_resolved(): void
    {
        foreach ([PropertyLifecycleEventSerializer::class, PostgreSqlPropertyLifecycleEventInbox::class, PropertyLifecycleEventDestination::class, DurablePropertyLifecycleEventRouter::class, PropertyLifecycleEventRouter::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $inbox = $this->app->make(PostgreSqlPropertyLifecycleEventInbox::class);
        $destination = $this->app->make(PropertyLifecycleEventDestination::class);
        $implementation = $this->app->make(DurablePropertyLifecycleEventRouter::class);
        $router = $this->app->make(PropertyLifecycleEventRouter::class);

        self::assertSame($inbox, $destination);
        self::assertSame($implementation, $router);
        self::assertSame($destination, new \ReflectionProperty($implementation, 'destination')->getValue($implementation));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($inbox, 'connection')->getValue($inbox));
        self::assertSame($this->app->make(PropertyLifecycleEventSerializer::class), new \ReflectionProperty($inbox, 'serializer')->getValue($inbox));
        self::assertStringNotContainsStringIgnoringCase('fake', $implementation::class);
        self::assertStringNotContainsStringIgnoringCase('null', $implementation::class);
    }

    public function test_runtime_health_remains_healthy_with_property_routing_capabilities(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();
        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }
}
