<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountRepository;
use PDO;
use ReflectionProperty;
use Tests\TestCase;

final class HistoricalAccountRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(PDO::class, static fn (): PDO => new PDO('sqlite::memory:'));
    }

    public function test_graph_is_unique_lazy_and_shares_the_certified_instances(): void
    {
        self::assertFalse($this->app->resolved(AccountPersistenceMapper::class));
        self::assertFalse($this->app->resolved(PostgreSqlAccountRepository::class));
        self::assertFalse($this->app->resolved(AccountRegistry::class));

        $registry = $this->app->make(AccountRegistry::class);
        self::assertInstanceOf(PostgreSqlAccountRepository::class, $registry);
        self::assertSame($registry, $this->app->make(PostgreSqlAccountRepository::class));
        self::assertSame($registry, $this->app->make(AccountRegistry::class));

        $mapper = (new ReflectionProperty($registry, 'mapper'))->getValue($registry);
        self::assertSame($this->app->make(AccountPersistenceMapper::class), $mapper);
        self::assertSame(
            $this->app->make(PDO::class),
            (new ReflectionProperty($registry, 'connection'))->getValue($registry),
        );
    }

    public function test_runtime_health_resolves_the_graph_without_query_or_transaction(): void
    {
        $connection = $this->app->make(PDO::class);
        self::assertFalse($connection->inTransaction());

        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertFalse($connection->inTransaction());
    }
}
