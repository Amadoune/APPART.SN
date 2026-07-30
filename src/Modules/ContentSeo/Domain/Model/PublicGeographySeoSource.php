<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

final readonly class PublicGeographySeoSource
{
    /** @param list<BreadcrumbItem> $breadcrumb */
    public function __construct(public ListingId $listingId, public string $locality, public array $breadcrumb)
    {
        if (trim($locality) === '' || trim($locality) !== $locality || $breadcrumb === []) {
            throw InvalidSeoValue::field('public_geography');
        }
    }
}
