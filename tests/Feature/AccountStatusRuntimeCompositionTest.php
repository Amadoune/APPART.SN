<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusWorkflow;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrationTransaction;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\DeterministicAccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract\AccountStatusWorkflowStore;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\AccountStatusWorkflowMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountRepository;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusOrchestrationTransaction;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusWorkflowStore;
use PDO;
use ReflectionProperty;
use Tests\TestCase;

final class AccountStatusRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(PDO::class, static fn (): PDO => new PDO('sqlite::memory:'));
    }

    public function test_graph_is_unique_lazy_and_shares_pdo_and_account_registry(): void
    {
        foreach ([
            AccountStatusWorkflow::class,
            AccountStatusWorkflowMapper::class,
            PostgreSqlAccountStatusWorkflowStore::class,
            AccountStatusWorkflowStore::class,
        ] as $component) {
            self::assertFalse($this->app->resolved($component));
        }

        $store = $this->app->make(AccountStatusWorkflowStore::class);
        self::assertInstanceOf(PostgreSqlAccountStatusWorkflowStore::class, $store);
        self::assertSame($store, $this->app->make(PostgreSqlAccountStatusWorkflowStore::class));
        self::assertSame($store, $this->app->make(AccountStatusWorkflowStore::class));
        self::assertSame(
            $this->app->make(PDO::class),
            (new ReflectionProperty($store, 'connection'))->getValue($store),
        );
        self::assertSame(
            $this->app->make(AccountRegistry::class),
            (new ReflectionProperty($store, 'accounts'))->getValue($store),
        );
        self::assertInstanceOf(
            PostgreSqlAccountRepository::class,
            (new ReflectionProperty($store, 'accounts'))->getValue($store),
        );
        self::assertSame(
            $this->app->make(AccountStatusWorkflowMapper::class),
            (new ReflectionProperty($store, 'mapper'))->getValue($store),
        );
    }

    public function test_runtime_health_is_healthy_at_fifty_eight_without_query_or_transaction(): void
    {
        $connection = $this->app->make(PDO::class);
        self::assertFalse($connection->inTransaction());

        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertCount(60, PublicProjectionRuntimeRequirements::certified());
        self::assertFalse($connection->inTransaction());
    }

    public function test_orchestrator_and_transaction_ports_are_unique_lazy_aliases(): void
    {
        self::assertFalse($this->app->resolved(AccountStatusOrchestrator::class));
        self::assertFalse($this->app->resolved(AccountStatusOrchestrationTransaction::class));

        self::assertSame(
            $this->app->make(DeterministicAccountStatusOrchestrator::class),
            $this->app->make(AccountStatusOrchestrator::class),
        );
        self::assertSame(
            $this->app->make(PostgreSqlAccountStatusOrchestrationTransaction::class),
            $this->app->make(AccountStatusOrchestrationTransaction::class),
        );
        self::assertSame(
            $this->app->make(PDO::class),
            (new ReflectionProperty(
                $this->app->make(PostgreSqlAccountStatusOrchestrationTransaction::class),
                'connection',
            ))->getValue($this->app->make(PostgreSqlAccountStatusOrchestrationTransaction::class)),
        );
    }
}
