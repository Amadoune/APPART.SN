<?php

namespace App\Application\PropertyAuthoringSourceCompleteness;

use App\Application\PropertyAuthoringGeographySelection\Contract\GeographySelectionReplayValidatorV1;
use App\Application\PropertyAuthoringGeographySelection\GeographySelectionReplayStatus;
use App\Application\PropertyAuthoringSourceCompleteness\Contract\PropertyAuthoringStateEnricherV1;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\AddressIntentId;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use Throwable;

final readonly class DeterministicPropertyAuthoringStateEnricherV1 implements PropertyAuthoringStateEnricherV1
{
    public function __construct(private GeographySelectionReplayValidatorV1 $geography) {}

    public function enrich(string $propertyId, string $ownerAccountId, int $version, string $intentId, ?PropertyAuthoringState $current, array $input): PropertyAuthoringEnrichmentResult
    {
        try {
            $placeId = $this->string($input, 'geographicPlaceId', $current?->geographicPlaceId);
            if (array_key_exists('geographicPlaceId', $input)) {
                $geography = $this->geography->validate(
                    $placeId ?? '',
                    $this->requiredString($input, 'geographicPlaceType'),
                    $this->nullableString($input, 'geographicParentPlaceId'),
                    $this->nullableString($input, 'geographicSelectionCursor'),
                    $this->requiredInt($input, 'geographicSelectionLimit'),
                );
                if ($geography->status === GeographySelectionReplayStatus::DependencyUnavailable) {
                    return new PropertyAuthoringEnrichmentResult(PropertyAuthoringEnrichmentStatus::DependencyUnavailable);
                }
                if ($geography->status !== GeographySelectionReplayStatus::Validated) {
                    return new PropertyAuthoringEnrichmentResult(PropertyAuthoringEnrichmentStatus::Invalid);
                }
            }

            $reference = $this->string($input, 'propertyReference', $current?->propertyReference);
            $surface = $this->integer($input, 'surfaceSquareMeters', $current?->surfaceSquareMeters);
            $rooms = $this->integer($input, 'rooms', $current?->rooms);
            $bathrooms = $this->integer($input, 'bathrooms', $current?->bathrooms);
            $year = $this->integer($input, 'constructionYear', $current?->constructionYear);
            $line = $this->string($input, 'addressLine', $current?->addressLine);
            $propertyType = $this->string($input, 'propertyType', $current?->propertyType);

            $placeId = $placeId === null ? null : PlaceId::fromString($placeId)->value;
            $reference = $reference === null ? null : PropertyReference::fromString($reference)->value;
            $surface = $surface === null ? null : SurfaceArea::fromSquareMeters($surface)->squareMeters;
            $rooms = $rooms === null ? null : RoomCount::fromInt($rooms)->value;
            $bathrooms = $bathrooms === null ? null : BathroomCount::fromInt($bathrooms)->value;
            $year = $year === null ? null : ConstructionYear::fromInt($year)->value;
            $line = $line === null ? null : AddressLine::fromString($line)->value;
            $propertyType = $propertyType === null ? null : PropertyType::from($propertyType)->value;

            $addressIntentId = $current?->addressIntentId;
            $physicalAddressChanged = $current === null
                || $current->geographicPlaceId !== $placeId
                || $current->addressLine !== $line
                || $addressIntentId === null;
            if ($placeId !== null && $line !== null && $physicalAddressChanged) {
                $addressIntentId = $this->uuidV4();
            }
            if ($addressIntentId !== null) {
                $addressIntentId = AddressIntentId::fromString($addressIntentId)->value;
            }

            $facts = [
                'addressIntentId' => $addressIntentId,
                'addressLine' => $line,
                'bathrooms' => $bathrooms,
                'city' => $this->string($input, 'city', $current?->city),
                'constructionYear' => $year,
                'geographicPlaceId' => $placeId,
                'neighborhood' => $this->string($input, 'neighborhood', $current?->neighborhood),
                'propertyReference' => $reference,
                'propertyType' => $propertyType,
                'rooms' => $rooms,
                'surfaceSquareMeters' => $surface,
            ];
            $checksum = hash('sha256', (string) json_encode([
                'contractVersion' => 'property-authoring-source-completeness-v1',
                'facts' => $facts,
                'ownerAccountId' => strtolower($ownerAccountId),
                'propertyId' => strtolower($propertyId),
                'version' => $version,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return new PropertyAuthoringEnrichmentResult(PropertyAuthoringEnrichmentStatus::Validated, new PropertyAuthoringState(
                $propertyId,
                $ownerAccountId,
                $version,
                $intentId,
                $checksum,
                $facts['propertyType'],
                $facts['city'],
                $facts['neighborhood'],
                $reference,
                $surface,
                $rooms,
                $bathrooms,
                $year,
                $placeId,
                $line,
                $addressIntentId,
            ));
        } catch (Throwable) {
            return new PropertyAuthoringEnrichmentResult(PropertyAuthoringEnrichmentStatus::Invalid);
        }
    }

    /** @param array<string, mixed> $input */
    private function string(array $input, string $key, ?string $fallback): ?string
    {
        if (! array_key_exists($key, $input)) {
            return $fallback;
        }
        if ($input[$key] === null) {
            return null;
        }
        if (! is_string($input[$key])) {
            throw new \InvalidArgumentException('Invalid string source.');
        }

        return $input[$key];
    }

    /** @param array<string, mixed> $input */
    private function integer(array $input, string $key, ?int $fallback): ?int
    {
        if (! array_key_exists($key, $input)) {
            return $fallback;
        }
        if ($input[$key] === null) {
            return null;
        }
        if (! is_int($input[$key])) {
            throw new \InvalidArgumentException('Invalid integer source.');
        }

        return $input[$key];
    }

    /** @param array<string, mixed> $input */
    private function requiredString(array $input, string $key): string
    {
        $value = $this->nullableString($input, $key);
        if ($value === null || $value === '') {
            throw new \InvalidArgumentException('Missing selection proof.');
        }

        return $value;
    }

    /** @param array<string, mixed> $input */
    private function nullableString(array $input, string $key): ?string
    {
        $value = $input[$key] ?? null;
        if ($value !== null && ! is_string($value)) {
            throw new \InvalidArgumentException('Invalid selection proof.');
        }

        return $value;
    }

    /** @param array<string, mixed> $input */
    private function requiredInt(array $input, string $key): int
    {
        $value = $input[$key] ?? null;
        if (! is_int($value)) {
            throw new \InvalidArgumentException('Invalid selection proof.');
        }

        return $value;
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    }
}
