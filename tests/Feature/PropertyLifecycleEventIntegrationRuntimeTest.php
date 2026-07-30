<?php

namespace Tests\Feature;

use App\Application\PropertyLifecycleEventIntegration\AtomicPropertyLifecycleEventOrchestrator;
use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleAtomicTransaction;
use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleEventOrchestrator;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PropertyLifecycleEventIntegrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_event_orchestrator_and_existing_transaction_are_lazy_unique_bindings(): void
    {
        foreach ([PropertyLifecycleAtomicTransaction::class, AtomicPropertyLifecycleEventOrchestrator::class, PropertyLifecycleEventOrchestrator::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $transaction = $this->app->make(PropertyLifecycleAtomicTransaction::class);
        $implementation = $this->app->make(AtomicPropertyLifecycleEventOrchestrator::class);
        $orchestrator = $this->app->make(PropertyLifecycleEventOrchestrator::class);

        self::assertInstanceOf(PostgreSqlAggregateOutboxTransaction::class, $transaction);
        self::assertSame($this->app->make(PostgreSqlAggregateOutboxTransaction::class), $transaction);
        self::assertSame($implementation, $orchestrator);
        self::assertSame($transaction, new \ReflectionProperty($implementation, 'transaction')->getValue($implementation));
        self::assertStringNotContainsStringIgnoringCase('fake', $implementation::class);
    }

    public function test_runtime_health_remains_healthy_without_executing_transition(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }
}
