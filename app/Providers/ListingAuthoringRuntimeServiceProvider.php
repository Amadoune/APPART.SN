<?php

namespace App\Providers;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\AuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingDraftStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingOwnershipStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingDraftStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingOwnershipStore;
use Illuminate\Support\ServiceProvider;

final class ListingAuthoringRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlListingDraftStore::class);
        $this->app->alias(PostgreSqlListingDraftStore::class, ListingDraftStore::class);
        $this->app->singleton(PostgreSqlListingOwnershipStore::class);
        $this->app->alias(PostgreSqlListingOwnershipStore::class, ListingOwnershipStore::class);
        $this->app->singleton(PostgreSqlAuthoringPortfolioStore::class);
        $this->app->alias(PostgreSqlAuthoringPortfolioStore::class, AuthoringPortfolioStore::class);
    }
}
