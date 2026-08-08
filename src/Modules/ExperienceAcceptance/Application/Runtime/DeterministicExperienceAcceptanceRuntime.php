<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Runtime;

final readonly class DeterministicExperienceAcceptanceRuntime implements ExperienceAcceptanceRuntimeV1
{
    private const RUNTIME_ID = 'experience-acceptance.owner-source';

    private const VERSION = 'experience-acceptance-runtime-v1';

    public function __construct(private ExperienceAcceptanceRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): ExperienceAcceptanceRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): ExperienceAcceptanceRuntimeDiagnostics
    {
        return new ExperienceAcceptanceRuntimeDiagnostics(self::RUNTIME_ID, self::VERSION, $this->availability());
    }
}
