<?php

namespace App\Providers;

use App\Infrastructure\ListingPublication\RegistryListingMediaCatalog;
use App\Infrastructure\ListingPublication\RegistryListingPropertyCatalog;
use Appart\Modules\ListingLifecycle\Application\Contract\MediaCatalog;
use Appart\Modules\ListingLifecycle\Application\Contract\PropertyCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationExpiration\Contract\ListingPublicationExpirationPolicyV1;
use Appart\Modules\ListingLifecycle\Application\PublicationExpiration\NinetyDayListingPublicationExpirationPolicy;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\Contract\ListingRevisionAllocatorV1;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\DeterministicListingRevisionAllocatorV1;
use Illuminate\Support\ServiceProvider;

final class ListingRevisionAuthorityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicListingRevisionAllocatorV1::class);
        $this->app->alias(DeterministicListingRevisionAllocatorV1::class, ListingRevisionAllocatorV1::class);
        $this->app->singleton(NinetyDayListingPublicationExpirationPolicy::class);
        $this->app->alias(NinetyDayListingPublicationExpirationPolicy::class, ListingPublicationExpirationPolicyV1::class);
        $this->app->singleton(RegistryListingPropertyCatalog::class);
        $this->app->alias(RegistryListingPropertyCatalog::class, PropertyCatalog::class);
        $this->app->singleton(RegistryListingMediaCatalog::class);
        $this->app->alias(RegistryListingMediaCatalog::class, MediaCatalog::class);
    }
}
