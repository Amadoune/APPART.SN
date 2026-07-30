<?php

namespace App\Application\ModerationRuntime;

use App\Application\ModerationRuntime\Contract\ModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeReport;

final readonly class DeterministicModerationRuntimeAvailabilityPolicy implements ModerationRuntimeAvailabilityPolicy
{
    /** @param array<string, bool> $registrations */
    public function __construct(private array $registrations) {}

    public function inspect(): ModerationRuntimeReport
    {
        $diagnostics = [
            'case_store' => ModerationRuntimeDiagnosticCode::CaseStoreMissing,
            'decision_store' => ModerationRuntimeDiagnosticCode::DecisionStoreMissing,
            'queue_store' => ModerationRuntimeDiagnosticCode::QueueStoreMissing,
        ];
        foreach ($diagnostics as $binding => $diagnostic) {
            if (($this->registrations[$binding] ?? false) === false) {
                return new ModerationRuntimeReport(ModerationRuntimeStatus::Unavailable, $diagnostic);
            }
        }

        return new ModerationRuntimeReport(ModerationRuntimeStatus::Healthy);
    }
}
