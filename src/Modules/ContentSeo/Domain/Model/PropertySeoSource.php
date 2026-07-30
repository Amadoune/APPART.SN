<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;

final readonly class PropertySeoSource
{
    public function __construct(public ListingId $listingId, public PropertySeoState $state, public string $propertyType, public string $city, public SeoSourceRevision $revision) {}
}
