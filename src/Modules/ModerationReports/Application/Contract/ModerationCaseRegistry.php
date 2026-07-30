<?php

namespace Appart\Modules\ModerationReports\Application\Contract;

use Appart\Modules\ModerationReports\Domain\Model\ModerationCase;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;

interface ModerationCaseRegistry
{
    /** Returns a detached Aggregate without persisted events. */
    public function find(ModerationCaseId $id): ?ModerationCase;

    /** Atomically adds a unique case and permanently reserves its first report. */
    public function addWithReportReservation(ModerationCase $case, ReportId $reportId): void;

    /** Conditionally saves when expectedVersion matches. */
    public function save(ModerationCase $case, int $expectedVersion): void;

    /** Atomically reserves evidence identity and conditionally saves; no state is visible on failure. */
    public function saveWithReportReservation(ModerationCase $case, ReportId $id, int $expectedVersion): void;

    public function saveWithFindingReservation(ModerationCase $case, FindingId $id, int $expectedVersion): void;

    public function saveWithDecisionReservation(ModerationCase $case, DecisionId $id, int $expectedVersion): void;
}
