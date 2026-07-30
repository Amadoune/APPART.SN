<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Domain\Model\PublicMediaSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface PublicMediaCatalog
{
    public function seoSourceFor(ListingId $id): ?PublicMediaSeoSource;
}
