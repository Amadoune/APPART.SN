<?php

namespace Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingOwnershipState;

interface ListingOwnershipStore
{
    public function read(string $listingId): ?ListingOwnershipState;

    public function save(ListingOwnershipState $state, int $expectedVersion): AuthoringPersistenceWriteResult;
}
