<?php

namespace Tests\Feature;

use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventOrchestrator;
use App\Application\AdministrativeActionLifecycleEventIntegration\Contract\AdministrativeActionLifecycleAtomicTransaction;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class AdministrativeActionLifecycleAtomicEventIntegrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_atomic_integrator_and_transaction_are_unique_and_lazy(): void
    {
        foreach ([
            PostgreSqlAggregateOutboxTransaction::class,
            AdministrativeActionLifecycleAtomicTransaction::class,
            AdministrativeActionLifecycleAtomicEventOrchestrator::class,
        ] as $component) {
            self::assertTrue($this->app->bound($component));
            self::assertFalse($this->app->resolved($component));
        }

        self::assertSame(
            $this->app->make(PostgreSqlAggregateOutboxTransaction::class),
            $this->app->make(AdministrativeActionLifecycleAtomicTransaction::class),
        );
        self::assertSame(
            $this->app->make(AdministrativeActionLifecycleAtomicEventOrchestrator::class),
            $this->app->make(AdministrativeActionLifecycleAtomicEventOrchestrator::class),
        );
    }

    public function test_runtime_health_remains_healthy_at_fifty(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
    }
}
