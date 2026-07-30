<?php

namespace Appart\Modules\Media\Domain\Event;

use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;

final readonly class MediaArchived extends AbstractMediaCollectionEvent
{
    public function __construct(MediaCollectionId $id, public MediaId $mediaId, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
