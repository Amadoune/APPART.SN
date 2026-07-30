<?php

namespace Appart\Modules\ContentSeo\Domain\Event;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoFailSafeReason;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use DateTimeImmutable;

final readonly class SeoFailSafeApplied extends AbstractSeoEvent
{
    public function __construct(SeoProjectionId $id, ListingId $listing, public SeoFailSafeReason $reason, DateTimeImmutable $at)
    {
        parent::__construct($id, $listing, $at);
    }
}
