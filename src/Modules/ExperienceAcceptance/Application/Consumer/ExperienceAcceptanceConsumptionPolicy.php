<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Consumer;

use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRoutingResult;

final readonly class ExperienceAcceptanceConsumptionPolicy
{
    public function accepts(ExperienceAcceptanceRoutingResult $routing): bool
    {
        return $routing->destination->value === str_replace('.observed', '', $routing->envelope->type->value);
    }
}
