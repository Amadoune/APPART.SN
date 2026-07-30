<?php

namespace Tests\Unit\Modules\SearchDiscovery\Support;

use Appart\Modules\SearchDiscovery\Application\Contract\PropertyCatalog;
use Appart\Modules\SearchDiscovery\Domain\Model\PropertyProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

final class FakePropertyCatalog implements PropertyCatalog
{
    /** @var array<string,PropertyProjectionSource> */
    private array $items = [];

    public function set(ListingId $id, PropertyProjectionSource $source): void
    {
        $this->items[$id->value] = $source;
    }

    public function projectionFor(ListingId $id): ?PropertyProjectionSource
    {
        return $this->items[$id->value] ?? null;
    }
}
