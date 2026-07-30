<?php

namespace Appart\Modules\ModerationReports\Domain\Model;

use Appart\Modules\ModerationReports\Domain\Event\AbstractModerationCaseEvent;
use Appart\Modules\ModerationReports\Domain\Event\DecisionIssued;
use Appart\Modules\ModerationReports\Domain\Event\FindingRecorded;
use Appart\Modules\ModerationReports\Domain\Event\ModerationCaseClosed;
use Appart\Modules\ModerationReports\Domain\Event\ModerationCaseEvent;
use Appart\Modules\ModerationReports\Domain\Event\ReportCreated;
use Appart\Modules\ModerationReports\Domain\Event\ReportValidated;
use Appart\Modules\ModerationReports\Domain\Exception\InvalidModerationValue;
use Appart\Modules\ModerationReports\Domain\Exception\ModerationViolation;
use Appart\Modules\ModerationReports\Domain\Exception\PolicyViolation;
use Appart\Modules\ModerationReports\Domain\Policy\ModerationCaseClosurePolicy;
use Appart\Modules\ModerationReports\Domain\Policy\ModerationDecisionPolicy;
use Appart\Modules\ModerationReports\Domain\Policy\ReportValidationPolicy;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionType;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingSeverity;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReporterId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportReason;
use DateTimeImmutable;

final class ModerationCase
{
    /** @var array<string,Report> */
    private array $reports = [];

    /** @var array<string,ModerationFinding> */
    private array $findings = [];

    /** @var array<string,ModerationDecision> */
    private array $decisions = [];

    /** @var list<ModerationCaseEvent> */
    private array $events = [];

    private bool $closed = false;

    private int $version = 0;

    private ?DecisionId $currentDecisionId = null;

    private function __construct(private readonly ModerationCaseId $id, private readonly ListingId $listingId, private DateTimeImmutable $lastChangedAt) {}

    public static function open(ModerationCaseId $id, ListingId $listingId, ReportId $reportId, ReporterId $reporterId, ReportReason $reason, DateTimeImmutable $at): self
    {
        $case = new self($id, $listingId, $at);
        $case->reports[$reportId->value] = Report::create($reportId, $reporterId, $reason, $at);
        $case->record(new ReportCreated($id, $listingId, $reportId, $reporterId, $reason, $at), 0);

        return $case;
    }

    /**
     * @param  list<Report>  $reports
     * @param  list<ModerationFinding>  $findings
     * @param  list<ModerationDecision>  $decisions
     */
    public static function reconstitute(ModerationCaseId $id, ListingId $listingId, DateTimeImmutable $lastChangedAt, array $reports, array $findings, array $decisions, bool $closed, ?DecisionId $currentDecisionId, int $version): self
    {
        if ($version < 0) {
            throw InvalidModerationValue::field('version');
        }
        $case = new self($id, $listingId, $lastChangedAt);
        foreach ($reports as $report) {
            $case->reports[$report->id->value] = $report;
        }
        foreach ($findings as $finding) {
            $case->findings[$finding->id->value] = $finding;
        }
        foreach ($decisions as $decision) {
            $case->decisions[$decision->id->value] = $decision;
        }
        if ($currentDecisionId !== null && ! isset($case->decisions[$currentDecisionId->value])) {
            throw InvalidModerationValue::field('current_decision');
        }
        $case->closed = $closed;
        $case->currentDecisionId = $currentDecisionId;
        $case->version = $version;

        return $case;
    }

    public function addReport(ListingId $listingId, ReportId $id, ReporterId $reporter, ReportReason $reason, DateTimeImmutable $at): void
    {
        $this->guard($at);
        if (! $this->listingId->equals($listingId)) {
            throw ModerationViolation::missing('case listing');
        }if (isset($this->reports[$id->value])) {
            throw ModerationViolation::duplicate('report');
        }$this->reports[$id->value] = Report::create($id, $reporter, $reason, $at);
        $this->record(new ReportCreated($this->id, $this->listingId, $id, $reporter, $reason, $at));
        $this->changed($at);
    }

    public function validateReport(ReportId $id, ReportValidationAssessment $assessment, ModerationRationale $rationale, ModeratorId $moderator, DateTimeImmutable $at, ReportValidationPolicy $policy): void
    {
        $this->guard($at);
        $report = $this->reports[$id->value] ?? throw ModerationViolation::missing('report');
        $policy->assertValid($report, $moderator, $assessment);
        $this->reports[$id->value] = $report->validate($assessment, $rationale, $moderator, $at);
        $this->record(new ReportValidated($this->id, $this->listingId, $id, $assessment, $rationale, $moderator, $at));
        $this->changed($at);
    }

    /** @param list<ReportId> $reportIds */
    public function recordFinding(FindingId $id, array $reportIds, FindingSeverity $severity, ModerationRationale $rationale, ModeratorId $moderator, DateTimeImmutable $at): void
    {
        $this->guard($at);
        if (isset($this->findings[$id->value])) {
            throw ModerationViolation::duplicate('finding');
        }if ($reportIds === []) {
            throw new PolicyViolation('A finding requires at least one report.');
        }$seen = [];
        foreach ($reportIds as $reportId) {
            if (isset($seen[$reportId->value])) {
                throw new PolicyViolation('Duplicate report reference.');
            }$seen[$reportId->value] = true;
            $report = $this->reports[$reportId->value] ?? throw ModerationViolation::missing('report');
            if (! $report->isAccepted()) {
                throw new PolicyViolation('A finding requires accepted reports.');
            }if ($report->reporterId->value === $moderator->value || $report->validatedBy?->value === $moderator->value) {
                throw new PolicyViolation('An involved report actor cannot record its finding.');
            }
        }$this->findings[$id->value] = new ModerationFinding($id, $reportIds, $severity, $rationale, $moderator, $at);
        $this->record(new FindingRecorded($this->id, $this->listingId, $id, $reportIds, $severity, $rationale, $moderator, $at));
        $this->changed($at);
    }

    /** @param list<FindingId> $findingIds */
    public function issueDecision(DecisionId $id, array $findingIds, DecisionType $type, ModerationRationale $rationale, ModeratorId $moderator, ?DecisionId $supersedes, DateTimeImmutable $at, ModerationDecisionPolicy $policy): void
    {
        $this->guard($at);
        if (isset($this->decisions[$id->value])) {
            throw ModerationViolation::duplicate('decision');
        }$current = $this->currentDecision();
        $policy->assertCanIssue($findingIds, $supersedes, $moderator, $current, $this->findings, $this->reports);
        $decision = new ModerationDecision($id, $findingIds, $type, $rationale, $moderator, $at, $supersedes);
        $this->decisions[$id->value] = $decision;
        $this->currentDecisionId = $id;
        $this->record(new DecisionIssued($this->id, $this->listingId, $id, $findingIds, $type, $rationale, $moderator, $supersedes, $at));
        $this->changed($at);
    }

    public function close(ModeratorId $moderator, ModerationRationale $rationale, DateTimeImmutable $at, ModerationCaseClosurePolicy $policy): void
    {
        $this->guard($at);
        $current = $this->currentDecision();
        $policy->assertCanClose($this->reports, $this->findings, $current, $moderator);
        $this->closed = true;
        $this->record(new ModerationCaseClosed($this->id, $this->listingId, $current->id, $moderator, $rationale, $at));
        $this->changed($at);
    }

    public function id(): ModerationCaseId
    {
        return $this->id;
    }

    public function listingId(): ListingId
    {
        return $this->listingId;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function currentDecision(): ?ModerationDecision
    {
        return $this->currentDecisionId === null ? null : $this->decisions[$this->currentDecisionId->value];
    }

    /** @return list<Report> */
    public function reports(): array
    {
        return array_values($this->reports);
    }

    /** @return list<ModerationFinding> */
    public function findings(): array
    {
        return array_values($this->findings);
    }

    /** @return list<ModerationDecision> */
    public function decisions(): array
    {
        return array_values($this->decisions);
    }

    /** @return list<ModerationCaseEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function guard(DateTimeImmutable $at): void
    {
        if ($this->closed) {
            throw ModerationViolation::closed();
        }if ($at < $this->lastChangedAt) {
            throw InvalidModerationValue::field('event_time');
        }
    }

    private function changed(DateTimeImmutable $at): void
    {
        $this->lastChangedAt = $at;
        $this->version++;
    }

    private function record(ModerationCaseEvent $event, ?int $resultVersion = null): void
    {
        if ($event instanceof AbstractModerationCaseEvent) {
            $event->stamp($resultVersion ?? $this->version + 1, count($this->events) + 1);
        }
        $this->events[] = $event;
    }
}
