<?php

namespace App\Application\PropertyAuthoringSourceCompleteness;

use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;

final readonly class PropertyAuthoringEnrichmentResult
{
    public function __construct(
        public PropertyAuthoringEnrichmentStatus $status,
        public ?PropertyAuthoringState $state = null,
    ) {}
}
