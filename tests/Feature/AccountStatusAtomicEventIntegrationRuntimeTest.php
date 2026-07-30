<?php

namespace Tests\Feature;

use App\Application\AccountStatusEventIntegration\AccountStatusAtomicEventOrchestrator;
use App\Application\AccountStatusEventIntegration\Contract\AccountStatusAtomicTransaction;
use App\Application\AccountStatusEventRouting\AccountStatusEventRouter;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class AccountStatusAtomicEventIntegrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_atomic_graph_resolves_lazily_with_one_transaction_instance(): void
    {
        $transaction = $this->app->make(AccountStatusAtomicTransaction::class);

        self::assertInstanceOf(PostgreSqlAggregateOutboxTransaction::class, $transaction);
        self::assertSame(
            $transaction,
            $this->app->make(PostgreSqlAggregateOutboxTransaction::class),
        );
        self::assertSame(
            $this->app->make(AccountStatusEventRouter::class),
            $this->app->make(AccountStatusEventRouter::class),
        );
        self::assertSame(
            $this->app->make(AccountStatusAtomicEventOrchestrator::class),
            $this->app->make(AccountStatusAtomicEventOrchestrator::class),
        );
    }

    public function test_runtime_health_remains_healthy_at_fifty_eight(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertCount(60, PublicProjectionRuntimeRequirements::certified());
    }
}
