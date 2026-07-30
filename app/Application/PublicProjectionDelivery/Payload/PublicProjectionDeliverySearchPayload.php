<?php

namespace App\Application\PublicProjectionDelivery\Payload;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadChecksum;
use InvalidArgumentException;

final readonly class PublicProjectionDeliverySearchPayload implements PublicProjectionDeliveryPayload
{
    public function __construct(public string $listingId)
    {
        if ($listingId === '' || trim($listingId) !== $listingId) {
            throw new InvalidArgumentException('Invalid Search delivery payload.');
        }
    }

    public function fields(): array
    {
        return ['listingId' => $this->listingId];
    }

    public function checksum(): string
    {
        return PublicProjectionDeliveryPayloadChecksum::calculate($this->fields());
    }
}
