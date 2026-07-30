<?php

namespace App\Providers;

use App\Application\PropertyListingAuthoringOperations\Contract\PropertyListingAuthoringOperations;
use App\Application\PropertyListingAuthoringOperations\DeterministicPropertyListingAuthoringOperations;
use App\Infrastructure\Authoring\PropertyAuthoringCatalogAdapter;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\CreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Application\Creation\DeterministicCreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingTransaction;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class PropertyListingAuthoringOperationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlListingTransaction::class);
        $this->app->alias(PostgreSqlListingTransaction::class, ListingCreationTransaction::class);
        $this->app->singleton(PostgreSqlListingCreationIntentStore::class);
        $this->app->alias(PostgreSqlListingCreationIntentStore::class, ListingCreationIntentStore::class);
        $this->app->singleton(PropertyAuthoringCatalogAdapter::class);
        $this->app->singleton(
            DeterministicCreateListingDraftV1::class,
            static fn (Application $app): DeterministicCreateListingDraftV1 => new DeterministicCreateListingDraftV1(
                new CreateDraft(
                    $app->make(ListingRegistry::class),
                    $app->make(PropertyAuthoringCatalogAdapter::class),
                    new ListingTransitionPolicy,
                ),
                $app->make(ListingCreationIntentStore::class),
                $app->make(ListingCreationTransaction::class),
            ),
        );
        $this->app->alias(DeterministicCreateListingDraftV1::class, CreateListingDraftV1::class);
        $this->app->singleton(DeterministicPropertyListingAuthoringOperations::class);
        $this->app->alias(DeterministicPropertyListingAuthoringOperations::class, PropertyListingAuthoringOperations::class);
    }
}
