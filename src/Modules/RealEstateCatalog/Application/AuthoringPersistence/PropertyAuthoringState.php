<?php

namespace Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence;

final readonly class PropertyAuthoringState
{
    public function __construct(
        public string $propertyId,
        public string $ownerAccountId,
        public int $version,
        public string $intentId,
        public string $intentChecksum,
        public ?string $propertyType = null,
        public ?string $city = null,
        public ?string $neighborhood = null,
        public ?string $propertyReference = null,
        public ?int $surfaceSquareMeters = null,
        public ?int $rooms = null,
        public ?int $bathrooms = null,
        public ?int $constructionYear = null,
        public ?string $geographicPlaceId = null,
        public ?string $addressLine = null,
        public ?string $addressIntentId = null,
    ) {}

    public function completeness(): PropertyAuthoringSourceCompleteness
    {
        return $this->propertyReference !== null
            && $this->propertyType !== null
            && $this->surfaceSquareMeters !== null
            && $this->rooms !== null
            && $this->bathrooms !== null
            && $this->geographicPlaceId !== null
            && $this->addressLine !== null
            && $this->addressIntentId !== null
                ? PropertyAuthoringSourceCompleteness::CompleteForPromotion
                : PropertyAuthoringSourceCompleteness::IncompleteForPromotion;
    }
}
