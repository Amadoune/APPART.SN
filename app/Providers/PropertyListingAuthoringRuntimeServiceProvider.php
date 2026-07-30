<?php

namespace App\Providers;

use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeAvailabilityPolicy;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Application\PropertyListingAuthoringRuntime\DeterministicPropertyListingAuthoringRuntimeAvailabilityPolicy;
use App\Application\PropertyListingAuthoringRuntime\DeterministicPropertyListingAuthoringRuntimeV1;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract\AccountAvailabilityInspector;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\AuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingDraftStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingOwnershipStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class PropertyListingAuthoringRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            PropertyListingAuthoringRuntimeAvailabilityPolicy::class,
            static fn (Application $app): PropertyListingAuthoringRuntimeAvailabilityPolicy => new DeterministicPropertyListingAuthoringRuntimeAvailabilityPolicy([
                'property_authoring' => self::compatible($app, PropertyAuthoringStore::class),
                'listing_draft' => self::compatible($app, ListingDraftStore::class),
                'listing_ownership' => self::compatible($app, ListingOwnershipStore::class),
                'authoring_portfolio' => self::compatible($app, AuthoringPortfolioStore::class),
                'account_availability_v1' => self::compatible($app, AccountAvailabilityInspector::class),
            ]),
        );
        $this->app->singleton(DeterministicPropertyListingAuthoringRuntimeV1::class);
        $this->app->alias(DeterministicPropertyListingAuthoringRuntimeV1::class, PropertyListingAuthoringRuntimeV1::class);
    }

    private static function compatible(Application $app, string $contract): bool
    {
        return $app->bound($contract) && $app->make($contract) instanceof $contract;
    }
}
