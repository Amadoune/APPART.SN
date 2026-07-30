<?php

namespace Tests\Unit\Modules\SearchDiscovery\Support;

use Appart\Modules\SearchDiscovery\Application\Contract\ListingCatalog;
use Appart\Modules\SearchDiscovery\Domain\Model\ListingProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

final class FakeListingCatalog implements ListingCatalog
{
    /** @var array<string,ListingProjectionSource> */
    private array $items = [];

    public function set(ListingId $id, ListingProjectionSource $source): void
    {
        $this->items[$id->value] = $source;
    }

    public function projectionFor(ListingId $id): ?ListingProjectionSource
    {
        return $this->items[$id->value] ?? null;
    }
}
