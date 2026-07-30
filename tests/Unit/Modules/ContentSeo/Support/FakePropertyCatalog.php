<?php

namespace Tests\Unit\Modules\ContentSeo\Support;

use Appart\Modules\ContentSeo\Application\Contract\PropertyCatalog;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

final class FakePropertyCatalog implements PropertyCatalog
{
    /** @var array<string, PropertySeoSource> */
    private array $items = [];

    public function set(PropertySeoSource $source): void
    {
        $this->items[$source->listingId->value] = $source;
    }

    public function seoSourceFor(ListingId $id): ?PropertySeoSource
    {
        return $this->items[$id->value] ?? null;
    }

    public function remove(ListingId $id): void
    {
        unset($this->items[$id->value]);
    }
}
