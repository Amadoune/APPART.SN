<?php

namespace Appart\Modules\ContentSeo\Domain\Event;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use DateTimeImmutable;

interface SeoEvent
{
    public function projectionId(): SeoProjectionId;

    public function listingId(): ListingId;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
