<?php

namespace App\Application\PublicProjectionDelivery\Payload;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadChecksum;
use InvalidArgumentException;

final readonly class PublicProjectionDeliveryPropertyPayload implements PublicProjectionDeliveryPayload
{
    public function __construct(public string $propertyId)
    {
        if ($propertyId === '' || trim($propertyId) !== $propertyId) {
            throw new InvalidArgumentException('Invalid property delivery payload.');
        }
    }

    public function fields(): array
    {
        return ['propertyId' => $this->propertyId];
    }

    public function checksum(): string
    {
        return PublicProjectionDeliveryPayloadChecksum::calculate($this->fields());
    }
}
