<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract\ReservationLifecycleWorkflowStore;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationLifecycleWorkflowRepository;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationLifecycleWorkflowMapper;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ReservationLifecycleRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_complete_graph_is_lazy_unique_and_resolved_by_laravel(): void
    {
        $components = [
            ReservationLifecycleWorkflow::class,
            ReservationLifecycleWorkflowMapper::class,
            PostgreSqlReservationLifecycleWorkflowRepository::class,
            ReservationLifecycleWorkflowStore::class,
        ];
        foreach ($components as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $workflow = $this->app->make(ReservationLifecycleWorkflow::class);
        $mapper = $this->app->make(ReservationLifecycleWorkflowMapper::class);
        $repository = $this->app->make(PostgreSqlReservationLifecycleWorkflowRepository::class);
        $store = $this->app->make(ReservationLifecycleWorkflowStore::class);

        self::assertSame($workflow, $this->app->make(ReservationLifecycleWorkflow::class));
        self::assertSame($repository, $store);
        self::assertSame($repository, $this->app->make(PostgreSqlReservationLifecycleWorkflowRepository::class));
        self::assertSame($mapper, new \ReflectionProperty($repository, 'mapper')->getValue($repository));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($repository, 'connection')->getValue($repository));
        self::assertStringNotContainsStringIgnoringCase('fake', $repository::class);
        self::assertStringNotContainsStringIgnoringCase('null', $repository::class);
    }

    public function test_runtime_health_reports_both_capabilities_as_healthy_without_business_execution(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }
}
