<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\PublicMediaUrl;

final readonly class PublicMediaSeoSource
{
    public function __construct(public ListingId $listingId, public PublicMediaUrl $url) {}
}
