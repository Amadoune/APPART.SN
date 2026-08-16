<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion;

use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;

final readonly class PromotionCommandRecord
{
    public function __construct(
        public string $commandId,
        public string $checksum,
        public string $propertyId,
        public string $ownerAccountId,
        public int $authoringVersion,
        public string $occurredAt,
    ) {}

    public function matches(PromoteAuthoredPropertyCommand $command, PropertyAuthoringState $snapshot): bool
    {
        return hash_equals($this->propertyId, strtolower($command->propertyId))
            && hash_equals($this->ownerAccountId, strtolower($command->ownerAccountId))
            && $this->authoringVersion === $command->expectedAuthoringVersion
            && hash_equals($this->occurredAt, PromotionCommandChecksum::instant($command->occurredAt))
            && hash_equals($this->checksum, PromotionCommandChecksum::for($command, $snapshot));
    }
}
