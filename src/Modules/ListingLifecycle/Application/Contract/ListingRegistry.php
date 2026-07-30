<?php

namespace Appart\Modules\ListingLifecycle\Application\Contract;

use Appart\Modules\ListingLifecycle\Domain\Exception\ConcurrentListingModification;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingIdConflict;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface ListingRegistry
{
    /** Returns a detached Aggregate without previously persisted events. */
    public function find(ListingId $id): ?Listing;

    /** @throws ListingIdConflict */
    public function add(Listing $listing): void;

    /** @throws ConcurrentListingModification */
    public function save(Listing $listing, int $expectedVersion): void;
}
