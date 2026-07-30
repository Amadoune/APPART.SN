<?php

namespace App\Application\MediaIngestionRuntime;

use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeAvailabilityPolicy;
use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeReport;

final readonly class DeterministicMediaIngestionRuntimeAvailabilityPolicy implements MediaIngestionRuntimeAvailabilityPolicy
{
    /** @param array<string, bool> $registrations */
    public function __construct(private array $registrations) {}

    public function inspect(): MediaIngestionRuntimeReport
    {
        foreach ($this->registrations as $code => $available) {
            if (! $available) {
                return new MediaIngestionRuntimeReport(
                    MediaIngestionRuntimeStatus::MissingBinding,
                    $code,
                );
            }
        }

        return new MediaIngestionRuntimeReport(MediaIngestionRuntimeStatus::Ready);
    }
}
