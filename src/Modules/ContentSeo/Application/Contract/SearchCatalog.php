<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface SearchCatalog
{
    public function seoSourceFor(ListingId $id): ?SearchSeoSource;
}
