<?php

namespace Tests\Feature;

use App\Application\MediaItemLifecycleEventIntegration\Contract\MediaItemLifecycleAtomicTransaction;
use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventOrchestrator;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class MediaItemLifecycleAtomicEventIntegrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_atomic_integrator_and_transaction_are_unique_and_lazy(): void
    {
        foreach ([PostgreSqlAggregateOutboxTransaction::class, MediaItemLifecycleAtomicTransaction::class, MediaItemLifecycleAtomicEventOrchestrator::class] as $component) {
            self::assertTrue($this->app->bound($component));
            self::assertFalse($this->app->resolved($component));
        }

        self::assertSame($this->app->make(PostgreSqlAggregateOutboxTransaction::class), $this->app->make(MediaItemLifecycleAtomicTransaction::class));
        self::assertSame($this->app->make(MediaItemLifecycleAtomicEventOrchestrator::class), $this->app->make(MediaItemLifecycleAtomicEventOrchestrator::class));
    }

    public function test_runtime_health_remains_healthy_at_forty_five(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
    }
}
