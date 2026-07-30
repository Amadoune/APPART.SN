<?php

namespace Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingDraftState;

interface ListingDraftStore
{
    public function read(string $listingId): ?ListingDraftState;

    public function save(ListingDraftState $state, int $expectedVersion): AuthoringPersistenceWriteResult;
}
