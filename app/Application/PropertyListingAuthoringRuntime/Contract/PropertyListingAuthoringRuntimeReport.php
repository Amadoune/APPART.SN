<?php

namespace App\Application\PropertyListingAuthoringRuntime\Contract;

use App\Application\PropertyListingAuthoringRuntime\PropertyListingAuthoringRuntimeStatus;

final readonly class PropertyListingAuthoringRuntimeReport
{
    public function __construct(
        public PropertyListingAuthoringRuntimeStatus $status,
        public ?string $code = null,
    ) {}
}
