<?php

namespace Tests\Unit\Contracts\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface ListingRegistryHarness
{
    public function freshRegistry(): ListingRegistry;

    public function minimalListing(?ListingId $id = null): Listing;

    public function listingWithHistory(?ListingId $id = null): Listing;

    public function archivedListing(?ListingId $id = null): Listing;

    public function primaryId(): ListingId;

    public function distinctId(): ListingId;

    public function mutate(Listing $listing): void;

    public function failNextWrite(ListingRegistry $registry): void;
}
