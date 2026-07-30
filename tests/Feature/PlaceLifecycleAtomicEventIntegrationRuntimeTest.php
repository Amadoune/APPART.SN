<?php

namespace Tests\Feature;

use App\Application\PlaceLifecycleEventIntegration\Contract\PlaceLifecycleAtomicTransaction;
use App\Application\PlaceLifecycleEventIntegration\PlaceLifecycleAtomicEventOrchestrator;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PlaceLifecycleAtomicEventIntegrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_atomic_integration_is_lazy_and_reuses_the_generic_transaction_instance(): void
    {
        self::assertTrue($this->app->bound(PlaceLifecycleAtomicTransaction::class));
        self::assertTrue($this->app->bound(PlaceLifecycleAtomicEventOrchestrator::class));
        self::assertFalse($this->app->resolved(PlaceLifecycleAtomicEventOrchestrator::class));

        $transaction = $this->app->make(PostgreSqlAggregateOutboxTransaction::class);
        self::assertSame($transaction, $this->app->make(PlaceLifecycleAtomicTransaction::class));
        self::assertSame(
            $this->app->make(PlaceLifecycleAtomicEventOrchestrator::class),
            $this->app->make(PlaceLifecycleAtomicEventOrchestrator::class),
        );
    }
}
