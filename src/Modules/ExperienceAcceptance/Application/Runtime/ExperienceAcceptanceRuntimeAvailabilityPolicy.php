<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Runtime;

interface ExperienceAcceptanceRuntimeAvailabilityPolicy
{
    public function inspect(): ExperienceAcceptanceRuntimeAvailability;
}
