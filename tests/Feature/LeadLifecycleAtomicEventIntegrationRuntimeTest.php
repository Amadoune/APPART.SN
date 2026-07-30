<?php

namespace Tests\Feature;

use App\Application\LeadLifecycleEventIntegration\Contract\LeadLifecycleAtomicTransaction;
use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventOrchestrator;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class LeadLifecycleAtomicEventIntegrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_atomic_integrator_and_existing_transaction_are_unique_and_lazy(): void
    {
        self::assertTrue($this->app->bound(LeadLifecycleAtomicEventOrchestrator::class));
        self::assertFalse($this->app->resolved(LeadLifecycleAtomicEventOrchestrator::class));

        $integrator = $this->app->make(LeadLifecycleAtomicEventOrchestrator::class);
        $transaction = $this->app->make(LeadLifecycleAtomicTransaction::class);

        self::assertSame($integrator, $this->app->make(LeadLifecycleAtomicEventOrchestrator::class));
        self::assertSame($this->app->make(PostgreSqlAggregateOutboxTransaction::class), $transaction);
        self::assertSame($transaction, (new ReflectionProperty($integrator, 'transaction'))->getValue($integrator));
        self::assertStringNotContainsStringIgnoringCase('fake', $integrator::class);
    }

    public function test_runtime_health_remains_healthy_at_thirty_five_capabilities(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }
}
