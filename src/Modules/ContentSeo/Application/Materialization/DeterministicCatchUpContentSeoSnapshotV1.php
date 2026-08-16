<?php

namespace Appart\Modules\ContentSeo\Application\Materialization;

use Appart\Modules\ContentSeo\Application\Materialization\Contract\CatchUpContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\MaterializeContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

final readonly class DeterministicCatchUpContentSeoSnapshotV1 implements CatchUpContentSeoSnapshotV1
{
    public function __construct(private MaterializeContentSeoSnapshotV1 $materializer) {}

    public function catchUp(ListingId $listingId): ContentSeoMaterializationResult
    {
        return $this->materializer->materialize($listingId);
    }
}
