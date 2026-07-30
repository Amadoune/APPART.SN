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
    ) {}
}
