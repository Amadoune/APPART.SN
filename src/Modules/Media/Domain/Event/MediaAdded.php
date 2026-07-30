<?php

namespace Appart\Modules\Media\Domain\Event;

use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaSource;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use DateTimeImmutable;

final readonly class MediaAdded extends AbstractMediaCollectionEvent
{
    public function __construct(MediaCollectionId $id, public MediaId $mediaId, public MediaType $type, public MediaChecksum $checksum, public MediaOrder $order, public MediaSource $source, public bool $primary, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
