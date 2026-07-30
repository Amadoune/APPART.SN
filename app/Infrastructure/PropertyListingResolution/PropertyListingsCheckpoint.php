<?php

namespace App\Infrastructure\PropertyListingResolution;

use App\Application\PropertyListingResolution\PropertyListingsDiagnostic;
use JsonException;

final readonly class PropertyListingsCheckpoint
{
    public function encode(string $propertyId, string $after): string
    {
        $json = json_encode(['v' => 1, 'property' => hash('sha256', $propertyId), 'after' => $after], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    /** @return array{after?: string, diagnostic: PropertyListingsDiagnostic} */
    public function decode(string $propertyId, string $checkpoint): array
    {
        try {
            $padding = str_repeat('=', (4 - strlen($checkpoint) % 4) % 4);
            $json = base64_decode(strtr($checkpoint, '-_', '+/').$padding, true);
            if (! is_string($json)) {
                return ['diagnostic' => PropertyListingsDiagnostic::InvalidCheckpoint];
            }
            $payload = json_decode($json, true, 8, JSON_THROW_ON_ERROR);
            if (! is_array($payload) || ($payload['v'] ?? null) !== 1 || ! is_string($payload['property'] ?? null) || ! is_string($payload['after'] ?? null)) {
                return ['diagnostic' => PropertyListingsDiagnostic::InvalidCheckpoint];
            }
            if (! hash_equals(hash('sha256', $propertyId), $payload['property'])) {
                return ['diagnostic' => PropertyListingsDiagnostic::CheckpointForAnotherProperty];
            }
            if (! self::isUuid($payload['after'])) {
                return ['diagnostic' => PropertyListingsDiagnostic::InvalidCheckpoint];
            }

            return ['after' => $payload['after'], 'diagnostic' => PropertyListingsDiagnostic::None];
        } catch (JsonException) {
            return ['diagnostic' => PropertyListingsDiagnostic::InvalidCheckpoint];
        }
    }

    private static function isUuid(string $identity): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $identity) === 1;
    }
}
