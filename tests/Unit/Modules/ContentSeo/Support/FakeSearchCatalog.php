<?php

namespace Tests\Unit\Modules\ContentSeo\Support;

use Appart\Modules\ContentSeo\Application\Contract\SearchCatalog;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

final class FakeSearchCatalog implements SearchCatalog
{
    /** @var array<string, SearchSeoSource> */
    private array $items = [];

    public function set(SearchSeoSource $source): void
    {
        $this->items[$source->listingId->value] = $source;
    }

    public function seoSourceFor(ListingId $id): ?SearchSeoSource
    {
        return $this->items[$id->value] ?? null;
    }

    public function remove(ListingId $id): void
    {
        unset($this->items[$id->value]);
    }
}
