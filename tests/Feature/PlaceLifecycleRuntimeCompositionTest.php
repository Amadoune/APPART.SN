<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthComponent;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleWorkflow;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\Contract\PlaceLifecycleWorkflowStore;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceLifecycleWorkflowMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceLifecycleWorkflowStore;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PlaceLifecycleRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_graph_is_bound_once_lazy_and_resolves_shared_instances(): void
    {
        $components = [
            PlaceLifecycleWorkflow::class,
            PlaceLifecycleWorkflowMapper::class,
            PostgreSqlPlaceLifecycleWorkflowStore::class,
            PlaceLifecycleWorkflowStore::class,
        ];

        foreach ($components as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $workflow = $this->app->make(PlaceLifecycleWorkflow::class);
        $mapper = $this->app->make(PlaceLifecycleWorkflowMapper::class);
        $store = $this->app->make(PostgreSqlPlaceLifecycleWorkflowStore::class);
        $port = $this->app->make(PlaceLifecycleWorkflowStore::class);

        self::assertSame($workflow, $this->app->make(PlaceLifecycleWorkflow::class));
        self::assertSame($mapper, $this->app->make(PlaceLifecycleWorkflowMapper::class));
        self::assertSame($store, $port);
        self::assertSame($store, $this->app->make(PostgreSqlPlaceLifecycleWorkflowStore::class));
        self::assertSame($mapper, new \ReflectionProperty($store, 'mapper')->getValue($store));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($store, 'connection')->getValue($store));
    }

    public function test_runtime_health_is_additively_healthy_at_fifty_five_without_transaction(): void
    {
        $connection = $this->app->make(PDO::class);
        self::assertFalse($connection->inTransaction());

        $requirements = PublicProjectionRuntimeRequirements::certified();
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertGreaterThanOrEqual(55, count($requirements));
        self::assertSame(RuntimeHealthComponent::PlaceLifecycleWorkflow, $requirements[52]->component);
        self::assertSame(RuntimeHealthComponent::PlaceLifecycleWorkflowStore, $requirements[53]->component);
        self::assertSame(RuntimeHealthComponent::PlaceLifecycleInboxStore, $requirements[54]->component);
        self::assertSame(RuntimeHealthComponent::PlaceLifecycleEventRouter, $requirements[55]->component);
        self::assertSame(RuntimeHealthComponent::PlaceLifecycleDeliveryConsumer, $requirements[56]->component);
        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertFalse($connection->inTransaction());
    }
}
