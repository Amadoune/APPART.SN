<?php

namespace App\Providers;

use App\Application\ListingPublicationCommandGateway\DeterministicListingPublicationCommandGateway;
use App\Infrastructure\Authoring\PropertyAuthoringCatalogAdapter;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\Contract\MediaCatalog;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandLedgerV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationGatewayTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Application\UseCase\PublishListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\SendToReview;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationGatewayPersistence;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class ListingPublicationCommandGatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlListingPublicationGatewayPersistence::class);
        $this->app->alias(PostgreSqlListingPublicationGatewayPersistence::class, ListingPublicationCommandLedgerV1::class);
        $this->app->alias(PostgreSqlListingPublicationGatewayPersistence::class, ListingPublicationGatewayTransaction::class);
        $this->app->when(DeterministicListingPublicationCommandGateway::class)
            ->needs(SendToReview::class)
            ->give(static fn (Application $app): SendToReview => new SendToReview(
                $app->make(ListingRegistry::class),
                $app->make(PropertyAuthoringCatalogAdapter::class),
                new ListingTransitionPolicy,
            ));
        $this->app->when(DeterministicListingPublicationCommandGateway::class)
            ->needs(PublishListing::class)
            ->give(static fn (Application $app): PublishListing => new PublishListing(
                $app->make(ListingRegistry::class),
                $app->make(PropertyAuthoringCatalogAdapter::class),
                new ListingTransitionPolicy,
                $app->make(MediaCatalog::class),
                $app->make(AuthoringPublicFactHandoffV1::class),
                $app->make(ListingCreationTransaction::class),
            ));
        $this->app->singleton(DeterministicListingPublicationCommandGateway::class);
        $this->app->alias(DeterministicListingPublicationCommandGateway::class, ListingPublicationCommandGatewayV1::class);
    }
}
