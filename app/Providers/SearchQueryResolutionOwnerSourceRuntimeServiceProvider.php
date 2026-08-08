<?php

namespace App\Providers;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\Contract\SearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\Contract\SearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\Contract\SearchQueryResolutionOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\DeterministicSearchQueryResolutionOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchQueryResolutionOwnerSourceMapper;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class SearchQueryResolutionOwnerSourceRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchQueryResolutionOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlSearchQueryResolutionOwnerSource::class,
            static fn ($app): PostgreSqlSearchQueryResolutionOwnerSource => new PostgreSqlSearchQueryResolutionOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(SearchQueryResolutionOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlSearchQueryResolutionOwnerSource::class, SearchQueryResolutionOwnerSource::class);
        $this->app->singleton(
            DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy => new DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy(
                $app->make(SearchQueryResolutionOwnerSource::class),
            ),
        );
        $this->app->alias(
            DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy::class,
            SearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy::class,
        );
        $this->app->singleton(DeterministicSearchQueryResolutionOwnerSourceRuntimeV1::class);
        $this->app->alias(DeterministicSearchQueryResolutionOwnerSourceRuntimeV1::class, SearchQueryResolutionOwnerSourceRuntimeV1::class);
    }
}
