<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion;

use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use DateTimeImmutable;
use DateTimeZone;

final readonly class PromotionCommandChecksum
{
    public static function for(PromoteAuthoredPropertyCommand $command, PropertyAuthoringState $snapshot): string
    {
        return hash('sha256', (string) json_encode([
            'contractVersion' => 'promote-authored-property-v1',
            'propertyId' => strtolower($command->propertyId),
            'ownerAccountId' => strtolower($command->ownerAccountId),
            'expectedAuthoringVersion' => $command->expectedAuthoringVersion,
            'snapshot' => [
                'propertyId' => $snapshot->propertyId,
                'ownerAccountId' => $snapshot->ownerAccountId,
                'version' => $snapshot->version,
                'propertyType' => $snapshot->propertyType,
                'propertyReference' => $snapshot->propertyReference,
                'surfaceSquareMeters' => $snapshot->surfaceSquareMeters,
                'rooms' => $snapshot->rooms,
                'bathrooms' => $snapshot->bathrooms,
                'constructionYear' => $snapshot->constructionYear,
                'geographicPlaceId' => $snapshot->geographicPlaceId,
                'addressLine' => $snapshot->addressLine,
                'addressIntentId' => $snapshot->addressIntentId,
            ],
            'occurredAt' => self::instant($command->occurredAt),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    public static function instant(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
