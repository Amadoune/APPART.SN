<?php

namespace Tests\Feature;

use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessAtomicTransaction;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\DeterministicIdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlIdentityAccessAtomicTransaction;
use PDO;
use ReflectionProperty;
use Tests\TestCase;

final class IdentityAccessRuntimeOrchestrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(PDO::class, static fn (): PDO => new PDO('sqlite::memory:'));
    }

    public function test_orchestrator_and_transaction_are_unique_lazy_aliases(): void
    {
        self::assertFalse($this->app->resolved(IdentityAccessOrchestrator::class));
        self::assertFalse($this->app->resolved(IdentityAccessAtomicTransaction::class));

        $transaction = $this->app->make(IdentityAccessAtomicTransaction::class);
        $orchestrator = $this->app->make(IdentityAccessOrchestrator::class);

        self::assertInstanceOf(PostgreSqlIdentityAccessAtomicTransaction::class, $transaction);
        self::assertInstanceOf(DeterministicIdentityAccessOrchestrator::class, $orchestrator);
        self::assertSame($transaction, $this->app->make(PostgreSqlIdentityAccessAtomicTransaction::class));
        self::assertSame($orchestrator, $this->app->make(DeterministicIdentityAccessOrchestrator::class));
        self::assertSame(
            $transaction,
            (new ReflectionProperty($orchestrator, 'transaction'))->getValue($orchestrator),
        );
        self::assertSame(
            $this->app->make(PDO::class),
            (new ReflectionProperty($transaction, 'connection'))->getValue($transaction),
        );
    }
}
