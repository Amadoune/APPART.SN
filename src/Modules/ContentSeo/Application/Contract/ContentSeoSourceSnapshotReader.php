<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadResult;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface ContentSeoSourceSnapshotReader
{
    public function readByListing(ListingId $listingId): ContentSeoSnapshotReadResult;
}
