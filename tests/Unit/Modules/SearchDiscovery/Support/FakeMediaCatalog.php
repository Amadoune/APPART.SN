<?php

namespace Tests\Unit\Modules\SearchDiscovery\Support;

use Appart\Modules\SearchDiscovery\Application\Contract\MediaCatalog;
use Appart\Modules\SearchDiscovery\Domain\Model\MediaProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

final class FakeMediaCatalog implements MediaCatalog
{
    /** @var array<string,MediaProjectionSource> */
    private array $items = [];

    public function set(ListingId $id, MediaProjectionSource $source): void
    {
        $this->items[$id->value] = $source;
    }

    public function projectionFor(ListingId $id): ?MediaProjectionSource
    {
        return $this->items[$id->value] ?? null;
    }
}
