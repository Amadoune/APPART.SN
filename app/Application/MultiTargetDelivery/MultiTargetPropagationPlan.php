<?php

namespace App\Application\MultiTargetDelivery;

use App\Application\PropertyListingResolution\PropertyListingsDiagnostic;

final readonly class MultiTargetPropagationPlan
{
    /** @param list<string> $listingIds */
    public function __construct(
        public MultiTargetPropagationRequest $request,
        public MultiTargetPropagationStatus $status,
        public array $listingIds,
        public ?string $nextCheckpoint,
        public bool $completed,
        public PropertyListingsDiagnostic $diagnostic,
    ) {}
}
