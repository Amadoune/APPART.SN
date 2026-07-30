<?php

namespace Appart\Modules\Media\Domain\Event;

use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use DateTimeImmutable;

interface MediaCollectionEvent
{
    public function collectionId(): MediaCollectionId;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
