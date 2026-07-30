<?php

namespace App\Application\ProfessionalProfileRuntime;

use App\Application\ProfessionalProfileRuntime\Contract\ProfessionalProfileRuntimeAvailabilityPolicy;
use App\Application\ProfessionalProfileRuntime\Contract\ProfessionalProfileRuntimeReport;

final readonly class DeterministicProfessionalProfileRuntimeAvailabilityPolicy implements ProfessionalProfileRuntimeAvailabilityPolicy
{
    /** @param array<string, bool> $registrations */
    public function __construct(private array $registrations) {}

    public function inspect(): ProfessionalProfileRuntimeReport
    {
        foreach ($this->registrations as $componentCode => $available) {
            if (! $available) {
                return new ProfessionalProfileRuntimeReport(
                    ProfessionalProfileRuntimeStatus::MissingBinding,
                    $componentCode,
                );
            }
        }

        return new ProfessionalProfileRuntimeReport(ProfessionalProfileRuntimeStatus::Ready);
    }
}
