<?php

namespace Appart\Modules\ContentSeo\Application\Materialization\Contract;

use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoMaterializationResult;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface CatchUpContentSeoSnapshotV1
{
    public function catchUp(ListingId $listingId): ContentSeoMaterializationResult;
}
