<?php

namespace Appart\Modules\ModerationReports\Domain\Policy;

use Appart\Modules\ModerationReports\Domain\Exception\PolicyViolation;
use Appart\Modules\ModerationReports\Domain\Model\ModerationDecision;
use Appart\Modules\ModerationReports\Domain\Model\ModerationFinding;
use Appart\Modules\ModerationReports\Domain\Model\Report;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;

final readonly class ModerationCaseClosurePolicy
{
    /**
     * @param  array<string,Report>  $reports
     * @param  array<string,ModerationFinding>  $findings
     */
    public function assertCanClose(array $reports, array $findings, ?ModerationDecision $current, ModeratorId $closer): void
    {
        if ($current === null || ! $current->isFinal()) {
            throw new PolicyViolation('A final current decision is required and no escalation may remain open.');
        }
        if ($current->issuedBy->value === $closer->value) {
            throw new PolicyViolation('The decision issuer cannot close the case.');
        }
        $coveredFindings = [];
        $coveredReports = [];
        foreach ($current->findingIds as $findingId) {
            $finding = $findings[$findingId->value] ?? throw new PolicyViolation('The current decision references an unknown finding.');
            $coveredFindings[$findingId->value] = true;
            foreach ($finding->reportIds as $reportId) {
                $coveredReports[$reportId->value] = true;
            }
        }
        foreach ($reports as $report) {
            if (! $report->isTreated()) {
                throw new PolicyViolation('Every report must be treated before closure.');
            }if ($report->reporterId->value === $closer->value || $report->validatedBy?->value === $closer->value) {
                throw new PolicyViolation('An involved report actor cannot close the case.');
            }if ($report->isAccepted() && ! isset($coveredReports[$report->id->value])) {
                throw new PolicyViolation('Every accepted report must support the final decision.');
            }
        }
        foreach ($findings as $finding) {
            if (! isset($coveredFindings[$finding->id->value])) {
                throw new PolicyViolation('Every finding must support the final decision.');
            }if ($finding->recordedBy->value === $closer->value) {
                throw new PolicyViolation('A finding author cannot close the case.');
            }
        }
    }
}
