<?php

namespace App\Providers;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\Contract\SearchOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\Contract\SearchOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\Contract\SearchOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\DeterministicSearchOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchOwnerSourceMapper;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class SearchOwnerSourceRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlSearchOwnerSource::class,
            static fn ($app): PostgreSqlSearchOwnerSource => new PostgreSqlSearchOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(SearchOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlSearchOwnerSource::class, SearchOwnerSource::class);
        $this->app->singleton(
            DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy => new DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy(
                $app->make(SearchOwnerSource::class),
            ),
        );
        $this->app->alias(
            DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy::class,
            SearchOwnerSourceRuntimeAvailabilityPolicy::class,
        );
        $this->app->singleton(DeterministicSearchOwnerSourceRuntimeV1::class);
        $this->app->alias(DeterministicSearchOwnerSourceRuntimeV1::class, SearchOwnerSourceRuntimeV1::class);
    }
}
