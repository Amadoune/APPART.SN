<?php

namespace App\Application\PropertyListingAuthoringRuntime;

use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeAvailabilityPolicy;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeReport;

final readonly class DeterministicPropertyListingAuthoringRuntimeAvailabilityPolicy implements PropertyListingAuthoringRuntimeAvailabilityPolicy
{
    /** @param array<string, bool> $registrations */
    public function __construct(private array $registrations) {}

    public function inspect(): PropertyListingAuthoringRuntimeReport
    {
        foreach ($this->registrations as $code => $available) {
            if (! $available) {
                return new PropertyListingAuthoringRuntimeReport(
                    PropertyListingAuthoringRuntimeStatus::MissingBinding,
                    $code,
                );
            }
        }

        return new PropertyListingAuthoringRuntimeReport(PropertyListingAuthoringRuntimeStatus::Ready);
    }
}
