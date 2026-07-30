<?php

namespace App\Application\PublicProjectionDelivery;

use JsonException;

final class PublicProjectionDeliveryPayloadChecksum
{
    /** @param array<string, bool|int|float|string|null> $fields */
    public static function calculate(array $fields): string
    {
        ksort($fields, SORT_STRING);

        try {
            return hash('sha256', json_encode($fields, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } catch (JsonException $error) {
            throw new \InvalidArgumentException('Public Projection Delivery payload is not canonically serializable.', previous: $error);
        }
    }
}
