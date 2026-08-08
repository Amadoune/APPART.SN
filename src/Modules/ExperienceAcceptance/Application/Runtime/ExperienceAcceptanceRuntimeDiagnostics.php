<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Runtime;

final readonly class ExperienceAcceptanceRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public ExperienceAcceptanceRuntimeAvailability $availability,
    ) {}
}
