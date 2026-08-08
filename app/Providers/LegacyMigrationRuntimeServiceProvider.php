<?php

namespace App\Providers;

use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\Runtime\DeterministicLegacyMigrationRuntime;
use Appart\Modules\LegacyMigration\Application\Runtime\DeterministicLegacyMigrationRuntimeAvailabilityPolicy;
use Appart\Modules\LegacyMigration\Application\Runtime\LegacyMigrationRuntimeAvailabilityPolicy;
use Appart\Modules\LegacyMigration\Application\Runtime\LegacyMigrationRuntimeV1;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\LegacyMigrationOwnerSourceMapper;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\PostgreSql\PostgreSqlLegacyMigrationOwnerSource;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class LegacyMigrationRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LegacyMigrationOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlLegacyMigrationOwnerSource::class,
            static fn ($app): PostgreSqlLegacyMigrationOwnerSource => new PostgreSqlLegacyMigrationOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(LegacyMigrationOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlLegacyMigrationOwnerSource::class, LegacyMigrationOwnerSource::class);
        $this->app->singleton(
            DeterministicLegacyMigrationRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicLegacyMigrationRuntimeAvailabilityPolicy => new DeterministicLegacyMigrationRuntimeAvailabilityPolicy(
                $app->make(LegacyMigrationOwnerSource::class),
            ),
        );
        $this->app->alias(DeterministicLegacyMigrationRuntimeAvailabilityPolicy::class, LegacyMigrationRuntimeAvailabilityPolicy::class);
        $this->app->singleton(DeterministicLegacyMigrationRuntime::class);
        $this->app->alias(DeterministicLegacyMigrationRuntime::class, LegacyMigrationRuntimeV1::class);
    }
}
