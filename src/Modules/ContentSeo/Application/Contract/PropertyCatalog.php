<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface PropertyCatalog
{
    public function seoSourceFor(ListingId $id): ?PropertySeoSource;
}
