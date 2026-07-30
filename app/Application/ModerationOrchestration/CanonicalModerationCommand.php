<?php

namespace App\Application\ModerationOrchestration;

use DateTimeInterface;

final class CanonicalModerationCommand
{
    public static function checksum(object $command): string
    {
        $payload = self::normalize(get_object_vars($command));

        return hash('sha256', json_encode((object) $payload, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }

    public static function deterministicUuid(string $scope, string $identity): string
    {
        $hex = substr(hash('sha256', $scope.'|'.$identity), 0, 32);
        $hex[12] = '5';
        $hex[16] = dechex((hexdec($hex[16]) & 0x3) | 0x8);

        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    }

    /**
     * @param  array<mixed>  $payload
     * @return array<mixed>
     */
    private static function normalize(array $payload): array
    {
        foreach ($payload as &$value) {
            if ($value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d\TH:i:s.uP');
            } elseif (is_array($value)) {
                $value = self::normalize($value);
            }
        }
        unset($value);
        if (! array_is_list($payload)) {
            ksort($payload, SORT_STRING);
        }

        return $payload;
    }
}
