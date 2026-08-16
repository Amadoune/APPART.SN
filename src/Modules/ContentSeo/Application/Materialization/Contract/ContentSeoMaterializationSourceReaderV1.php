<?php

namespace Appart\Modules\ContentSeo\Application\Materialization\Contract;

use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoMaterializationSourceResult;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface ContentSeoMaterializationSourceReaderV1
{
    public function read(ListingId $listingId): ContentSeoMaterializationSourceResult;
}
