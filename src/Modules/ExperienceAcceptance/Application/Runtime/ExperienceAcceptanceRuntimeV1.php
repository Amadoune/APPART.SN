<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Runtime;

interface ExperienceAcceptanceRuntimeV1
{
    public function availability(): ExperienceAcceptanceRuntimeAvailability;

    public function diagnostics(): ExperienceAcceptanceRuntimeDiagnostics;
}
