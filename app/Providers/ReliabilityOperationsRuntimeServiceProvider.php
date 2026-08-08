<?php

namespace App\Providers;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\Runtime\DeterministicReliabilityOperationsRuntime;
use Appart\Modules\ReliabilityOperations\Application\Runtime\DeterministicReliabilityOperationsRuntimeAvailabilityPolicy;
use Appart\Modules\ReliabilityOperations\Application\Runtime\ReliabilityOperationsRuntimeAvailabilityPolicy;
use Appart\Modules\ReliabilityOperations\Application\Runtime\ReliabilityOperationsRuntimeV1;
use Appart\Modules\ReliabilityOperations\Infrastructure\Persistence\PostgreSql\PostgreSqlReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Infrastructure\Persistence\ReliabilityOperationsOwnerSourceMapper;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class ReliabilityOperationsRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReliabilityOperationsOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlReliabilityOperationsOwnerSource::class,
            static fn ($app): PostgreSqlReliabilityOperationsOwnerSource => new PostgreSqlReliabilityOperationsOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(ReliabilityOperationsOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlReliabilityOperationsOwnerSource::class, ReliabilityOperationsOwnerSource::class);
        $this->app->singleton(
            DeterministicReliabilityOperationsRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicReliabilityOperationsRuntimeAvailabilityPolicy => new DeterministicReliabilityOperationsRuntimeAvailabilityPolicy(
                $app->make(ReliabilityOperationsOwnerSource::class),
            ),
        );
        $this->app->alias(DeterministicReliabilityOperationsRuntimeAvailabilityPolicy::class, ReliabilityOperationsRuntimeAvailabilityPolicy::class);
        $this->app->singleton(DeterministicReliabilityOperationsRuntime::class);
        $this->app->alias(DeterministicReliabilityOperationsRuntime::class, ReliabilityOperationsRuntimeV1::class);
    }
}
