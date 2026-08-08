<?php

namespace App\Providers;

use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationConsoleOwnerSource;
use Appart\Modules\AdministrationConsole\Application\Runtime\AdministrationConsoleRuntimeAvailabilityPolicy;
use Appart\Modules\AdministrationConsole\Application\Runtime\AdministrationConsoleRuntimeV1;
use Appart\Modules\AdministrationConsole\Application\Runtime\DeterministicAdministrationConsoleRuntime;
use Appart\Modules\AdministrationConsole\Application\Runtime\DeterministicAdministrationConsoleRuntimeAvailabilityPolicy;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\AdministrationConsoleOwnerSourceMapper;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationConsoleOwnerSource;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class AdministrationConsoleRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdministrationConsoleOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlAdministrationConsoleOwnerSource::class,
            static fn ($app): PostgreSqlAdministrationConsoleOwnerSource => new PostgreSqlAdministrationConsoleOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(AdministrationConsoleOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlAdministrationConsoleOwnerSource::class, AdministrationConsoleOwnerSource::class);
        $this->app->singleton(
            DeterministicAdministrationConsoleRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicAdministrationConsoleRuntimeAvailabilityPolicy => new DeterministicAdministrationConsoleRuntimeAvailabilityPolicy(
                $app->make(AdministrationConsoleOwnerSource::class),
            ),
        );
        $this->app->alias(DeterministicAdministrationConsoleRuntimeAvailabilityPolicy::class, AdministrationConsoleRuntimeAvailabilityPolicy::class);
        $this->app->singleton(DeterministicAdministrationConsoleRuntime::class);
        $this->app->alias(DeterministicAdministrationConsoleRuntime::class, AdministrationConsoleRuntimeV1::class);
    }
}
