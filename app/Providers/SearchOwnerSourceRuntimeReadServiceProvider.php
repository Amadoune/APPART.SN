<?php

namespace App\Providers;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\Contract\SearchOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\Contract\SearchOwnerSourceRuntimeReadV1;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\DeterministicSearchOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\DeterministicSearchOwnerSourceRuntimeReadV1;
use Illuminate\Support\ServiceProvider;

final class SearchOwnerSourceRuntimeReadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicSearchOwnerSourceRuntimeReadPolicy::class);
        $this->app->alias(DeterministicSearchOwnerSourceRuntimeReadPolicy::class, SearchOwnerSourceRuntimeReadPolicy::class);
        $this->app->singleton(DeterministicSearchOwnerSourceRuntimeReadV1::class);
        $this->app->alias(DeterministicSearchOwnerSourceRuntimeReadV1::class, SearchOwnerSourceRuntimeReadV1::class);
    }
}
