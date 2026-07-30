<?php

namespace App\Application\PublicProjectionDelivery\Contract;

interface PublicProjectionDeliveryPayload
{
    /** @return array<string, bool|int|float|string|null> */
    public function fields(): array;

    public function checksum(): string;
}
