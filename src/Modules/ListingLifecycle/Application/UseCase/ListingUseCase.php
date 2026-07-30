<?php

namespace Appart\Modules\ListingLifecycle\Application\UseCase;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\Contract\PropertyCatalog;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingNotFound;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

abstract readonly class ListingUseCase
{
    public function __construct(protected ListingRegistry $listings, protected PropertyCatalog $properties, protected ListingTransitionPolicy $policy) {}

    /** @return array{Listing, int} */
    protected function load(ListingId $id): array
    {
        $listing = $this->listings->find($id) ?? throw new ListingNotFound;

        return [$listing, $listing->version()];
    }
}
