<?php

namespace Appart\Modules\ModerationReports\Domain\Model;

use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingSeverity;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use DateTimeImmutable;

final readonly class ModerationFinding
{
    /** @param non-empty-list<ReportId> $reportIds */
    public function __construct(public FindingId $id, public array $reportIds, public FindingSeverity $severity, public ModerationRationale $rationale, public ModeratorId $recordedBy, public DateTimeImmutable $recordedAt) {}
}
