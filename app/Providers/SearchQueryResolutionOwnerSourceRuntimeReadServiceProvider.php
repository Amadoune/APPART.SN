<?php

namespace App\Providers;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\Contract\SearchQueryResolutionOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\Contract\SearchQueryResolutionOwnerSourceRuntimeReadV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\DeterministicSearchQueryResolutionOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1;
use Illuminate\Support\ServiceProvider;

final class SearchQueryResolutionOwnerSourceRuntimeReadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicSearchQueryResolutionOwnerSourceRuntimeReadPolicy::class);
        $this->app->alias(DeterministicSearchQueryResolutionOwnerSourceRuntimeReadPolicy::class, SearchQueryResolutionOwnerSourceRuntimeReadPolicy::class);
        $this->app->singleton(DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1::class);
        $this->app->alias(DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1::class, SearchQueryResolutionOwnerSourceRuntimeReadV1::class);
    }
}
