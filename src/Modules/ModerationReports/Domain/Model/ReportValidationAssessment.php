<?php

namespace Appart\Modules\ModerationReports\Domain\Model;

use Appart\Modules\ModerationReports\Domain\ValueObject\ReportAdmissibility;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportAuthenticity;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportCompleteness;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportHandlingDecision;

final readonly class ReportValidationAssessment
{
    public function __construct(public ReportAdmissibility $admissibility, public ReportCompleteness $completeness, public ReportAuthenticity $authenticity, public ReportHandlingDecision $handlingDecision) {}
}
