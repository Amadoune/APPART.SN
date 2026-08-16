<?php

namespace Appart\Modules\ContentSeo\Application\Materialization\Contract;

use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoMaterializationResult;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface MaterializeContentSeoSnapshotV1
{
    public function materialize(ListingId $listingId): ContentSeoMaterializationResult;
}
