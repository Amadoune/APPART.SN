<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Domain\Model\PublicGeographySeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface PublicGeographyCatalog
{
    public function seoSourceFor(ListingId $id): ?PublicGeographySeoSource;
}
