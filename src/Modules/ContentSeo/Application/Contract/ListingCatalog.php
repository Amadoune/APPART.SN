<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface ListingCatalog
{
    public function seoSourceFor(ListingId $id): ?ListingSeoSource;
}
