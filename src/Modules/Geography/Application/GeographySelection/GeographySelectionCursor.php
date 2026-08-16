<?php

namespace Appart\Modules\Geography\Application\GeographySelection;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use InvalidArgumentException;
use Throwable;

final readonly class GeographySelectionCursor
{
    private const int VERSION = 1;

    public function __construct(public string $normalizationKey, public PlaceId $placeId) {}

    public static function encode(PlaceType $type, ?PlaceId $parentId, GeographySelectionSourceItem $item): string
    {
        $payload = ['v' => self::VERSION, 'fingerprint' => self::fingerprint($type, $parentId), 'key' => $item->normalizationKey, 'placeId' => $item->placeId];
        $payload['checksum'] = hash('sha256', self::json($payload));

        return rtrim(strtr(base64_encode(self::json($payload)), '+/', '-_'), '=');
    }

    public static function decode(string $cursor, PlaceType $type, ?PlaceId $parentId): self
    {
        try {
            $encoded = strtr($cursor, '-_', '+/');
            $decoded = base64_decode($encoded.str_repeat('=', (4 - strlen($encoded) % 4) % 4), true);
            $payload = is_string($decoded) ? json_decode($decoded, true, 8, JSON_THROW_ON_ERROR) : null;
            if (! is_array($payload) || array_keys($payload) !== ['v', 'fingerprint', 'key', 'placeId', 'checksum']) {
                throw new InvalidArgumentException('Invalid Geography selection cursor.');
            }
            $unsigned = array_slice($payload, 0, 4, true);
            if ($payload['v'] !== self::VERSION || $payload['fingerprint'] !== self::fingerprint($type, $parentId) || ! is_string($payload['key']) || ! is_string($payload['placeId']) || ! is_string($payload['checksum']) || ! hash_equals(hash('sha256', self::json($unsigned)), $payload['checksum'])) {
                throw new InvalidArgumentException('Invalid Geography selection cursor.');
            }

            return new self($payload['key'], PlaceId::fromString($payload['placeId']));
        } catch (Throwable) {
            throw new InvalidArgumentException('Invalid Geography selection cursor.');
        }
    }

    private static function fingerprint(PlaceType $type, ?PlaceId $parentId): string
    {
        return hash('sha256', $type->value.'|'.($parentId === null ? 'root' : $parentId->value));
    }

    /** @param array<string, mixed> $payload */
    private static function json(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
