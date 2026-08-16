<?php

namespace App\Application\PropertyAuthoringSourceCompleteness\Contract;

use App\Application\PropertyAuthoringSourceCompleteness\PropertyAuthoringEnrichmentResult;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;

interface PropertyAuthoringStateEnricherV1
{
    /** @param array<string, mixed> $input */
    public function enrich(
        string $propertyId,
        string $ownerAccountId,
        int $version,
        string $intentId,
        ?PropertyAuthoringState $current,
        array $input,
    ): PropertyAuthoringEnrichmentResult;
}
