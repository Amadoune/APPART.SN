<?php

namespace App\Application\PropertyListingAuthoringRuntime;

use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeAvailabilityPolicy;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeReport;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\AuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingDraftStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingOwnershipStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;

final readonly class DeterministicPropertyListingAuthoringRuntimeV1 implements PropertyListingAuthoringRuntimeV1
{
    public function __construct(
        private PropertyAuthoringStore $propertyAuthoring,
        private ListingDraftStore $listingDraft,
        private ListingOwnershipStore $listingOwnership,
        private AuthoringPortfolioStore $authoringPortfolio,
        private PropertyListingAuthoringRuntimeAvailabilityPolicy $availability,
    ) {}

    public function propertyAuthoring(): PropertyAuthoringStore
    {
        return $this->propertyAuthoring;
    }

    public function listingDraft(): ListingDraftStore
    {
        return $this->listingDraft;
    }

    public function listingOwnership(): ListingOwnershipStore
    {
        return $this->listingOwnership;
    }

    public function authoringPortfolio(): AuthoringPortfolioStore
    {
        return $this->authoringPortfolio;
    }

    public function inspect(): PropertyListingAuthoringRuntimeReport
    {
        return $this->availability->inspect();
    }
}
