<?php

namespace App\Application\PropertyListingAuthoringRuntime\Contract;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\AuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingDraftStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingOwnershipStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;

interface PropertyListingAuthoringRuntimeV1
{
    public function propertyAuthoring(): PropertyAuthoringStore;

    public function listingDraft(): ListingDraftStore;

    public function listingOwnership(): ListingOwnershipStore;

    public function authoringPortfolio(): AuthoringPortfolioStore;

    public function inspect(): PropertyListingAuthoringRuntimeReport;
}
