<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\Contract\ReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\DeterministicReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract\ReservationLifecycleWorkflowStore;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ReservationLifecycleRuntimeOrchestrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_orchestrator_is_a_lazy_unique_alias_with_only_certified_dependencies(): void
    {
        foreach ([DeterministicReservationLifecycleEventOrchestrator::class, ReservationLifecycleEventOrchestrator::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $implementation = $this->app->make(DeterministicReservationLifecycleEventOrchestrator::class);
        $orchestrator = $this->app->make(ReservationLifecycleEventOrchestrator::class);

        self::assertSame($implementation, $orchestrator);
        self::assertSame($this->app->make(ReservationLifecycleWorkflow::class), new \ReflectionProperty($implementation, 'workflow')->getValue($implementation));
        self::assertSame($this->app->make(ReservationLifecycleWorkflowStore::class), new \ReflectionProperty($implementation, 'store')->getValue($implementation));
        self::assertStringNotContainsStringIgnoringCase('fake', $implementation::class);
    }

    public function test_runtime_health_remains_healthy_without_orchestrator_requirement_or_execution(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }
}
