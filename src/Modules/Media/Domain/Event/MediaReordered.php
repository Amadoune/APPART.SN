<?php

namespace Appart\Modules\Media\Domain\Event;

use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use DateTimeImmutable;

final readonly class MediaReordered extends AbstractMediaCollectionEvent
{
    /** @param array<string, int> $orders */
    public function __construct(MediaCollectionId $id, public array $orders, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
