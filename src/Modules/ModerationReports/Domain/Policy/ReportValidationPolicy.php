<?php

namespace Appart\Modules\ModerationReports\Domain\Policy;

use Appart\Modules\ModerationReports\Domain\Exception\PolicyViolation;
use Appart\Modules\ModerationReports\Domain\Model\Report;
use Appart\Modules\ModerationReports\Domain\Model\ReportValidationAssessment;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportAdmissibility;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportAuthenticity;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportCompleteness;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportHandlingDecision;

final readonly class ReportValidationPolicy
{
    public function assertValid(Report $report, ModeratorId $moderator, ReportValidationAssessment $assessment): void
    {
        if ($report->reporterId->value === $moderator->value) {
            throw new PolicyViolation('A reporter cannot validate their own report.');
        }
        $positive = $assessment->admissibility === ReportAdmissibility::Admissible && $assessment->completeness === ReportCompleteness::Complete && $assessment->authenticity === ReportAuthenticity::Sufficient;
        if (($assessment->handlingDecision === ReportHandlingDecision::TakeCharge) !== $positive) {
            throw new PolicyViolation('The handling decision is inconsistent with the assessment.');
        }
    }
}
