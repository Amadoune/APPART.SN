<?php

namespace Appart\Modules\ContentSeo\Domain\Event;

use Appart\Modules\ContentSeo\Domain\Model\SeoMaterial;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use DateTimeImmutable;

final readonly class SeoProjectionGenerated extends AbstractSeoEvent
{
    public function __construct(SeoProjectionId $id, ListingId $listing, public SeoMaterial $material, DateTimeImmutable $at)
    {
        parent::__construct($id, $listing, $at);
    }
}
