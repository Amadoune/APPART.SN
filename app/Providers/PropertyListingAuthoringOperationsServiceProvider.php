<?php

namespace App\Providers;

use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Application\PropertyAuthoringSourceCompleteness\Contract\PropertyAuthoringStateEnricherV1;
use App\Application\PropertyListingAuthoringOperations\Contract\PropertyListingAuthoringOperations;
use App\Application\PropertyListingAuthoringOperations\DeterministicPropertyListingAuthoringOperations;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Infrastructure\Authoring\PropertyAuthoringCatalogAdapter;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\CreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Application\Creation\DeterministicCreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\Contract\ListingRevisionAllocatorV1;
use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Application\UseCase\SubmitListing;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingTransaction;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromoteAuthoredPropertyV1;
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
                $app->make(ListingPublicationWorkflowStore::class),
            ),
        );
        $this->app->alias(DeterministicCreateListingDraftV1::class, CreateListingDraftV1::class);
        $this->app->singleton(
            DeterministicPropertyListingAuthoringOperations::class,
            static fn (Application $app): DeterministicPropertyListingAuthoringOperations => new DeterministicPropertyListingAuthoringOperations(
                $app->make(PropertyListingAuthoringRuntimeV1::class),
                $app->make(CreateListingDraftV1::class),
                $app->make(ListingCreationTransaction::class),
                $app->make(ListingPublicationEventOrchestrator::class),
                $app->make(PropertyAuthoringStateEnricherV1::class),
                $app->make(AuthoringPublicFactHandoffV1::class),
                new SubmitListing(
                    $app->make(ListingRegistry::class),
                    $app->make(PropertyAuthoringCatalogAdapter::class),
                    new ListingTransitionPolicy,
                ),
                $app->make(ListingRevisionAllocatorV1::class),
                $app->make(PromoteAuthoredPropertyV1::class),
            ),
        );
        $this->app->alias(DeterministicPropertyListingAuthoringOperations::class, PropertyListingAuthoringOperations::class);
    }
}
