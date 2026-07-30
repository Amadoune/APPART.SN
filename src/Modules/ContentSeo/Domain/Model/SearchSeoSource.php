<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;

final readonly class SearchSeoSource
{
    public function __construct(public ListingId $listingId, public SearchSeoState $state, public SeoSourceRevision $revision) {}
}
