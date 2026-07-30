<?php

namespace Appart\Modules\ModerationReports\Application\UseCase;

use Appart\Modules\ModerationReports\Application\Contract\ModerationCaseRegistry;
use Appart\Modules\ModerationReports\Domain\Model\ReportValidationAssessment;
use Appart\Modules\ModerationReports\Domain\Policy\ReportValidationPolicy;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use DateTimeImmutable;

final readonly class ValidateReport extends ModerationCaseUseCase
{
    public function __construct(ModerationCaseRegistry $cases, private ReportValidationPolicy $policy)
    {
        parent::__construct($cases);
    }

    public function execute(ModerationCaseId $caseId, ReportId $reportId, ReportValidationAssessment $assessment, ModerationRationale $rationale, ModeratorId $moderator, DateTimeImmutable $at): void
    {
        [$case,$version] = $this->load($caseId);
        $case->validateReport($reportId, $assessment, $rationale, $moderator, $at, $this->policy);
        $this->cases->save($case, $version);
    }
}
