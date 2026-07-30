<?php

namespace Tests\Unit\Modules\ModerationReports\Support;

use Appart\Modules\ModerationReports\Application\Contract\ModerationCaseRegistry;
use Appart\Modules\ModerationReports\Domain\Exception\ConcurrentModerationCaseModification;
use Appart\Modules\ModerationReports\Domain\Exception\EvidenceIdConflict;
use Appart\Modules\ModerationReports\Domain\Exception\ModerationCaseIdConflict;
use Appart\Modules\ModerationReports\Domain\Model\ModerationCase;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;

final class FakeModerationCaseRegistry implements ModerationCaseRegistry
{
    /** @var array<string,ModerationCase> */
    private array $cases = [];

    /** @var array<string,string> */
    private array $reports = [];

    /** @var array<string,string> */
    private array $findings = [];

    /** @var array<string,string> */
    private array $decisions = [];

    private bool $fail = false;

    public function find(ModerationCaseId $id): ?ModerationCase
    {
        return isset($this->cases[$id->value]) ? clone $this->cases[$id->value] : null;
    }

    public function addWithReportReservation(ModerationCase $case, ReportId $reportId): void
    {
        if (isset($this->cases[$case->id()->value])) {
            throw new ModerationCaseIdConflict;
        }if (isset($this->reports[$reportId->value])) {
            throw new EvidenceIdConflict;
        }$this->cases[$case->id()->value] = $this->clean($case);
        $this->reports[$reportId->value] = $case->id()->value;
    }

    public function save(ModerationCase $case, int $expectedVersion): void
    {
        $this->guard($case, $expectedVersion);
        $this->cases[$case->id()->value] = $this->clean($case);
    }

    public function saveWithReportReservation(ModerationCase $case, ReportId $id, int $expectedVersion): void
    {
        $this->reserve($case, $id->value, $expectedVersion, $this->reports);
    }

    public function saveWithFindingReservation(ModerationCase $case, FindingId $id, int $expectedVersion): void
    {
        $this->reserve($case, $id->value, $expectedVersion, $this->findings);
    }

    public function saveWithDecisionReservation(ModerationCase $case, DecisionId $id, int $expectedVersion): void
    {
        $this->reserve($case, $id->value, $expectedVersion, $this->decisions);
    }

    public function failNextSave(): void
    {
        $this->fail = true;
    }

    /** @param array<string,string> $owners */
    private function reserve(ModerationCase $case, string $id, int $expectedVersion, array &$owners): void
    {
        $this->guard($case, $expectedVersion);
        $owner = $owners[$id] ?? null;
        if ($owner !== null && $owner !== $case->id()->value) {
            throw new EvidenceIdConflict;
        }$this->cases[$case->id()->value] = $this->clean($case);
        $owners[$id] = $case->id()->value;
    }

    private function guard(ModerationCase $case, int $expectedVersion): void
    {
        if ($this->fail) {
            $this->fail = false;
            throw new ConcurrentModerationCaseModification;
        }$stored = $this->cases[$case->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentModerationCaseModification;
        }
    }

    private function clean(ModerationCase $case): ModerationCase
    {
        $snapshot = clone $case;
        $snapshot->releaseEvents();

        return $snapshot;
    }
}
