<?php

namespace App\Providers;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\Contract\MediaCatalog;
use Appart\Modules\ListingLifecycle\Application\Contract\PropertyCatalog;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Application\UseCase\PublishListing;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthoringPublicFactHandoff;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class AuthoringPublicFactHandoffServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlAuthoringPublicFactHandoff::class);
        $this->app->alias(PostgreSqlAuthoringPublicFactHandoff::class, AuthoringPublicFactHandoffV1::class);
        $this->app->singleton(PublishListing::class, static fn (Application $app): PublishListing => new PublishListing(
            $app->make(ListingRegistry::class),
            $app->make(PropertyCatalog::class),
            new ListingTransitionPolicy,
            $app->make(MediaCatalog::class),
            $app->make(AuthoringPublicFactHandoffV1::class),
            $app->make(ListingCreationTransaction::class),
        ));
    }
}
