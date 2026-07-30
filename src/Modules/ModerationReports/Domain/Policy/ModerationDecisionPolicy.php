<?php

namespace Appart\Modules\ModerationReports\Domain\Policy;

use Appart\Modules\ModerationReports\Domain\Exception\PolicyViolation;
use Appart\Modules\ModerationReports\Domain\Model\ModerationDecision;
use Appart\Modules\ModerationReports\Domain\Model\ModerationFinding;
use Appart\Modules\ModerationReports\Domain\Model\Report;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;

final readonly class ModerationDecisionPolicy
{
    /**
     * @param  list<FindingId>  $findingIds
     * @param  array<string,ModerationFinding>  $findings
     * @param  array<string,Report>  $reports
     */
    public function assertCanIssue(array $findingIds, ?DecisionId $supersedes, ModeratorId $issuer, ?ModerationDecision $current, array $findings, array $reports): void
    {
        if ($findingIds === []) {
            throw new PolicyViolation('A decision requires at least one finding.');
        }
        if ($current === null && $supersedes !== null) {
            throw new PolicyViolation('There is no current decision to supersede.');
        }
        if ($current !== null && ($supersedes === null || ! $current->id->equals($supersedes))) {
            throw new PolicyViolation('A new decision must explicitly supersede the current decision.');
        }
        $seen = [];
        foreach ($findingIds as $findingId) {
            if (isset($seen[$findingId->value])) {
                throw new PolicyViolation('Duplicate finding reference.');
            }$seen[$findingId->value] = true;
            $finding = $findings[$findingId->value] ?? throw new PolicyViolation('Unknown supporting finding.');
            if ($finding->recordedBy->value === $issuer->value) {
                throw new PolicyViolation('A finding author cannot decide their own finding.');
            }foreach ($finding->reportIds as $reportId) {
                $report = $reports[$reportId->value] ?? throw new PolicyViolation('Unknown supporting report.');
                if ($report->reporterId->value === $issuer->value || $report->validatedBy?->value === $issuer->value) {
                    throw new PolicyViolation('An involved actor cannot issue the decision.');
                }
            }
        }
    }
}
