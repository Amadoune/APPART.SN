<?php

namespace Appart\Modules\ContentSeo\Domain\Event;

use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use DateTimeImmutable;

final readonly class CanonicalChanged extends AbstractSeoEvent
{
    public function __construct(SeoProjectionId $id, ListingId $listing, public CanonicalUrl $previous, public CanonicalUrl $current, DateTimeImmutable $at)
    {
        parent::__construct($id, $listing, $at);
    }
}
