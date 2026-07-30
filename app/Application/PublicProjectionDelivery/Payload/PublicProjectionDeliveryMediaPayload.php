<?php

namespace App\Application\PublicProjectionDelivery\Payload;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadChecksum;
use InvalidArgumentException;

final readonly class PublicProjectionDeliveryMediaPayload implements PublicProjectionDeliveryPayload
{
    public function __construct(public string $mediaCollectionId)
    {
        if ($mediaCollectionId === '' || trim($mediaCollectionId) !== $mediaCollectionId) {
            throw new InvalidArgumentException('Invalid media delivery payload.');
        }
    }

    public function fields(): array
    {
        return ['mediaCollectionId' => $this->mediaCollectionId];
    }

    public function checksum(): string
    {
        return PublicProjectionDeliveryPayloadChecksum::calculate($this->fields());
    }
}
