<?php

namespace Appart\Modules\ModerationReports\Domain\Model;

use Appart\Modules\ModerationReports\Domain\Exception\InvalidModerationValue;
use Appart\Modules\ModerationReports\Domain\Exception\ModerationViolation;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReporterId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportHandlingDecision;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportReason;
use DateTimeImmutable;

final readonly class Report
{
    private function __construct(public ReportId $id, public ReporterId $reporterId, public ReportReason $reason, public DateTimeImmutable $createdAt, public ?ReportValidationAssessment $assessment = null, public ?ModerationRationale $validationRationale = null, public ?ModeratorId $validatedBy = null, public ?DateTimeImmutable $validatedAt = null) {}

    public static function create(ReportId $id, ReporterId $reporter, ReportReason $reason, DateTimeImmutable $at): self
    {
        return new self($id, $reporter, $reason, $at);
    }

    public function isTreated(): bool
    {
        return $this->assessment !== null;
    }

    public function isAccepted(): bool
    {
        return $this->assessment?->handlingDecision === ReportHandlingDecision::TakeCharge;
    }

    public function validate(ReportValidationAssessment $assessment, ModerationRationale $rationale, ModeratorId $moderator, DateTimeImmutable $at): self
    {
        if ($this->isTreated()) {
            throw ModerationViolation::alreadyValidated();
        }if ($at < $this->createdAt) {
            throw InvalidModerationValue::field('report_validation_time');
        }

        return new self($this->id, $this->reporterId, $this->reason, $this->createdAt, $assessment, $rationale, $moderator, $at);
    }
}
