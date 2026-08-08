<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Consumer;

use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRoutingResult;

interface ExperienceAcceptanceConsumerV1
{
    public function consume(ExperienceAcceptanceRoutingResult $routing): ExperienceAcceptanceConsumptionResult;
}
