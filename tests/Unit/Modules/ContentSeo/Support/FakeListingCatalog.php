<?php

namespace Tests\Unit\Modules\ContentSeo\Support;

use Appart\Modules\ContentSeo\Application\Contract\ListingCatalog;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

final class FakeListingCatalog implements ListingCatalog
{
    /** @var array<string, ListingSeoSource> */
    private array $items = [];

    public function set(ListingSeoSource $source): void
    {
        $this->items[$source->listingId->value] = $source;
    }

    public function seoSourceFor(ListingId $id): ?ListingSeoSource
    {
        return $this->items[$id->value] ?? null;
    }

    public function remove(ListingId $id): void
    {
        unset($this->items[$id->value]);
    }
}
