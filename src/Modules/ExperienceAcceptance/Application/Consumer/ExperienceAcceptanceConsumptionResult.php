<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Consumer;

use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRoutingResult;

final readonly class ExperienceAcceptanceConsumptionResult
{
    public function __construct(
        public ExperienceAcceptanceConsumptionStatus $status,
        public ExperienceAcceptanceRoutingResult $routing,
    ) {}
}
