<?php

namespace App\Providers;

use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessAtomicTransaction;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\DeterministicIdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlIdentityAccessAtomicTransaction;
use Illuminate\Support\ServiceProvider;

final class IdentityAccessOrchestrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlIdentityAccessAtomicTransaction::class);
        $this->app->alias(PostgreSqlIdentityAccessAtomicTransaction::class, IdentityAccessAtomicTransaction::class);
        $this->app->singleton(DeterministicIdentityAccessOrchestrator::class);
        $this->app->alias(DeterministicIdentityAccessOrchestrator::class, IdentityAccessOrchestrator::class);
    }
}
