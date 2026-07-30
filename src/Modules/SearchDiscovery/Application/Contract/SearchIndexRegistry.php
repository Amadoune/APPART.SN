<?php

namespace Appart\Modules\SearchDiscovery\Application\Contract;

use Appart\Modules\SearchDiscovery\Domain\Model\SearchIndex;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;

interface SearchIndexRegistry
{
    /** Returns a detached, reconstructible projection without previously published events. */
    public function find(SearchIndexId $id): ?SearchIndex;

    public function findByListing(ListingId $listingId): ?SearchIndex;

    public function add(SearchIndex $index): void;

    public function save(SearchIndex $index, int $expectedVersion): void;
}
/** Returns the unique detached projection owned by a listing. */
/** Atomically enforces unique SearchIndexId and at most one active projection per listing. */
/** Saves a clean snapshot only when the stored version equals expectedVersion. */
