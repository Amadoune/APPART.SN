<?php

namespace Appart\Modules\Media\Domain\Event;

use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;

final readonly class MediaMarkedPrimary extends AbstractMediaCollectionEvent
{
    public function __construct(MediaCollectionId $id, public ?MediaId $previousPrimaryId, public MediaId $primaryId, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
