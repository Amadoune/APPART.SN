<?php

namespace Appart\Modules\ContentSeo\Domain\Event;

use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use DateTimeImmutable;

final readonly class SitemapChanged extends AbstractSeoEvent
{
    public function __construct(
        SeoProjectionId $id,
        ListingId $listing,
        public bool $previouslyIncluded,
        public bool $currentlyIncluded,
        public CanonicalUrl $previousCanonical,
        public CanonicalUrl $currentCanonical,
        DateTimeImmutable $at,
    ) {
        parent::__construct($id, $listing, $at);
    }
}
