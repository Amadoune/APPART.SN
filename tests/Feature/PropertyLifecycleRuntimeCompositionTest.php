<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleWorkflowStore;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\DeterministicPropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleWorkflow;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyLifecycleWorkflowRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyLifecycleWorkflowMapper;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PropertyLifecycleRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_complete_property_lifecycle_graph_is_lazy_unique_and_resolved_by_laravel(): void
    {
        $components = [
            PropertyLifecycleWorkflow::class,
            PropertyLifecycleWorkflowMapper::class,
            PostgreSqlPropertyLifecycleWorkflowRepository::class,
            PropertyLifecycleWorkflowStore::class,
        ];
        foreach ($components as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $workflow = $this->app->make(PropertyLifecycleWorkflow::class);
        $mapper = $this->app->make(PropertyLifecycleWorkflowMapper::class);
        $repository = $this->app->make(PostgreSqlPropertyLifecycleWorkflowRepository::class);
        $store = $this->app->make(PropertyLifecycleWorkflowStore::class);

        self::assertSame($workflow, $this->app->make(PropertyLifecycleWorkflow::class));
        self::assertSame($repository, $store);
        self::assertSame($repository, $this->app->make(PostgreSqlPropertyLifecycleWorkflowRepository::class));
        self::assertSame($mapper, new \ReflectionProperty($repository, 'mapper')->getValue($repository));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($repository, 'connection')->getValue($repository));
        self::assertStringNotContainsStringIgnoringCase('fake', $repository::class);
        self::assertStringNotContainsStringIgnoringCase('null', $repository::class);
    }

    public function test_runtime_health_reports_property_lifecycle_capabilities_as_healthy_without_business_execution(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }

    public function test_orchestrator_is_a_lazy_unique_alias_with_exact_certified_dependencies(): void
    {
        foreach ([DeterministicPropertyLifecycleOrchestrator::class, PropertyLifecycleOrchestrator::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $implementation = $this->app->make(DeterministicPropertyLifecycleOrchestrator::class);
        $orchestrator = $this->app->make(PropertyLifecycleOrchestrator::class);

        self::assertSame($implementation, $orchestrator);
        self::assertSame($this->app->make(PropertyLifecycleWorkflow::class), new \ReflectionProperty($implementation, 'workflow')->getValue($implementation));
        self::assertSame($this->app->make(PropertyLifecycleWorkflowStore::class), new \ReflectionProperty($implementation, 'store')->getValue($implementation));
        self::assertStringNotContainsStringIgnoringCase('fake', $implementation::class);
    }
}
