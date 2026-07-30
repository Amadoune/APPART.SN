<?php

namespace Tests\Feature;

use App\Application\ReservationLifecycleEventIntegration\Contract\ReservationLifecycleAtomicTransaction;
use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventOrchestrator;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ReservationLifecycleAtomicEventIntegrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_atomic_orchestrator_and_existing_transaction_are_unique_lazy_production_components(): void
    {
        self::assertTrue($this->app->bound(ReservationLifecycleAtomicEventOrchestrator::class));
        self::assertFalse($this->app->resolved(ReservationLifecycleAtomicEventOrchestrator::class));
        $orchestrator = $this->app->make(ReservationLifecycleAtomicEventOrchestrator::class);
        $transaction = $this->app->make(ReservationLifecycleAtomicTransaction::class);

        self::assertSame($orchestrator, $this->app->make(ReservationLifecycleAtomicEventOrchestrator::class));
        self::assertSame($this->app->make(PostgreSqlAggregateOutboxTransaction::class), $transaction);
        self::assertSame($transaction, (new ReflectionProperty($orchestrator, 'transaction'))->getValue($orchestrator));
        self::assertStringNotContainsStringIgnoringCase('fake', $orchestrator::class);
    }

    public function test_runtime_health_remains_healthy_at_twenty_seven_capabilities(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }
}
