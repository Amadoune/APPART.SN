<?php

namespace Tests\Unit\Modules\ModerationReports;

use Appart\Modules\ModerationReports\Domain\Event\DecisionIssued;
use Appart\Modules\ModerationReports\Domain\Event\FindingRecorded;
use Appart\Modules\ModerationReports\Domain\Event\ModerationCaseClosed;
use Appart\Modules\ModerationReports\Domain\Event\ReportValidated;
use Appart\Modules\ModerationReports\Domain\Exception\PolicyViolation;
use Appart\Modules\ModerationReports\Domain\Model\ModerationCase;
use Appart\Modules\ModerationReports\Domain\Model\ReportValidationAssessment;
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
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportAdmissibility;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportAuthenticity;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportCompleteness;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReporterId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportHandlingDecision;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportReason;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ModerationCaseTest extends TestCase
{
    private ReportValidationPolicy $validationPolicy;

    private ModerationDecisionPolicy $decisionPolicy;

    private ModerationCaseClosurePolicy $closurePolicy;

    protected function setUp(): void
    {
        $this->validationPolicy = new ReportValidationPolicy;
        $this->decisionPolicy = new ModerationDecisionPolicy;
        $this->closurePolicy = new ModerationCaseClosurePolicy;
    }

    public function test_validation_records_complete_unambiguous_assessment_and_event(): void
    {
        $c = $this->case();
        $c->validateReport($this->reportId(), $this->acceptedAssessment(), $this->rationale(), $this->validator(), $this->at(1), $this->validationPolicy);
        $report = $c->reports()[0];
        $event = $c->releaseEvents()[1];
        self::assertTrue($report->isTreated());
        self::assertTrue($report->isAccepted());
        self::assertSame(ReportAdmissibility::Admissible, $report->assessment?->admissibility);
        self::assertSame(ReportCompleteness::Complete, $report->assessment?->completeness);
        self::assertSame(ReportAuthenticity::Sufficient, $report->assessment?->authenticity);
        self::assertSame(ReportHandlingDecision::TakeCharge, $report->assessment?->handlingDecision);
        self::assertInstanceOf(ReportValidated::class, $event);
        self::assertSame($report->assessment, $event->assessment);
        self::assertSame($this->listingId()->value, $event->listingId()->value);
        self::assertSame($this->validator()->value, $event->validatedBy->value);
    }

    public function test_auto_validation_is_refused_without_mutation(): void
    {
        $c = $this->case();
        $c->releaseEvents();
        $version = $c->version();
        $this->expectException(PolicyViolation::class);
        try {
            $c->validateReport($this->reportId(), $this->acceptedAssessment(), $this->rationale(), ModeratorId::fromString($this->reporter()->value), $this->at(1), $this->validationPolicy);
        } finally {
            self::assertFalse($c->reports()[0]->isTreated());
            self::assertSame($version, $c->version());
            self::assertSame([], $c->releaseEvents());
        }
    }

    public function test_inconsistent_validation_assessment_is_refused(): void
    {
        $c = $this->case();
        $assessment = new ReportValidationAssessment(ReportAdmissibility::Inadmissible, ReportCompleteness::Complete, ReportAuthenticity::Sufficient, ReportHandlingDecision::TakeCharge);
        $this->expectException(PolicyViolation::class);
        $c->validateReport($this->reportId(), $assessment, $this->rationale(), $this->validator(), $this->at(1), $this->validationPolicy);
    }

    public function test_finding_links_accepted_reports_and_event_contains_complete_proof(): void
    {
        $c = $this->acceptedCase();
        $c->releaseEvents();
        $c->recordFinding($this->findingId(), [$this->reportId()], FindingSeverity::High, $this->rationale(), $this->finder(), $this->at(2));
        $finding = $c->findings()[0];
        $event = $c->releaseEvents()[0];
        self::assertSame($this->reportId()->value, $finding->reportIds[0]->value);
        self::assertInstanceOf(FindingRecorded::class, $event);
        self::assertSame($finding->reportIds, $event->reportIds);
        self::assertSame($this->finder()->value, $event->recordedBy->value);
    }

    public function test_finding_cannot_use_dismissed_report(): void
    {
        $c = $this->case();
        $c->validateReport($this->reportId(), $this->dismissedAssessment(), $this->rationale(), $this->validator(), $this->at(1), $this->validationPolicy);
        $this->expectException(PolicyViolation::class);
        $c->recordFinding($this->findingId(), [$this->reportId()], FindingSeverity::Low, $this->rationale(), $this->finder(), $this->at(2));
    }

    public function test_auto_decision_is_refused_without_mutation(): void
    {
        $c = $this->caseWithFinding();
        $c->releaseEvents();
        $version = $c->version();
        $this->expectException(PolicyViolation::class);
        try {
            $c->issueDecision($this->decisionId(), [$this->findingId()], DecisionType::NoAction, $this->rationale(), ModeratorId::fromString($this->reporter()->value), null, $this->at(3), $this->decisionPolicy);
        } finally {
            self::assertSame([], $c->decisions());
            self::assertSame($version, $c->version());
            self::assertSame([], $c->releaseEvents());
        }
    }

    public function test_decision_links_finding_and_report_chain(): void
    {
        $c = $this->caseWithDecision(DecisionType::RecommendSuspension);
        $decision = $c->currentDecision();
        self::assertSame($this->findingId()->value, $decision?->findingIds[0]->value);
        self::assertSame($this->reportId()->value, $c->findings()[0]->reportIds[0]->value);
        $event = $c->releaseEvents()[3];
        self::assertInstanceOf(DecisionIssued::class, $event);
        self::assertSame($decision?->findingIds, $event->findingIds);
        self::assertNull($event->supersedesDecisionId);
    }

    public function test_contradictory_decision_requires_explicit_supersession(): void
    {
        $c = $this->caseWithDecision(DecisionType::NoAction);
        $c->releaseEvents();
        $version = $c->version();
        $this->expectException(PolicyViolation::class);
        try {
            $c->issueDecision($this->secondDecisionId(), [$this->findingId()], DecisionType::RecommendRejection, $this->rationale(), $this->decider(), null, $this->at(4), $this->decisionPolicy);
        } finally {
            self::assertSame($this->decisionId()->value, $c->currentDecision()?->id->value);
            self::assertSame($version, $c->version());
            self::assertSame([], $c->releaseEvents());
        }
    }

    public function test_replacement_preserves_history_and_changes_current_decision(): void
    {
        $c = $this->caseWithDecision(DecisionType::NoAction);
        $c->issueDecision($this->secondDecisionId(), [$this->findingId()], DecisionType::RecommendRejection, $this->rationale(), $this->secondDecider(), $this->decisionId(), $this->at(4), $this->decisionPolicy);
        self::assertCount(2, $c->decisions());
        self::assertSame($this->secondDecisionId()->value, $c->currentDecision()?->id->value);
        self::assertSame($this->decisionId()->value, $c->currentDecision()?->supersedesDecisionId?->value);
    }

    public function test_open_escalation_blocks_closure(): void
    {
        $c = $this->caseWithDecision(DecisionType::Escalate);
        $c->releaseEvents();
        $version = $c->version();
        $this->expectException(PolicyViolation::class);
        try {
            $c->close($this->closer(), $this->rationale(), $this->at(4), $this->closurePolicy);
        } finally {
            self::assertFalse($c->isClosed());
            self::assertSame($version, $c->version());
            self::assertSame([], $c->releaseEvents());
        }
    }

    public function test_unhandled_report_blocks_closure(): void
    {
        $c = $this->caseWithDecision(DecisionType::NoAction);
        $c->addReport($this->listingId(), $this->secondReportId(), ReporterId::fromString('reporter-2'), $this->reason(), $this->at(4));
        $this->expectException(PolicyViolation::class);
        $c->close($this->closer(), $this->rationale(), $this->at(5), $this->closurePolicy);
    }

    public function test_coherent_final_case_can_close_and_event_identifies_decision(): void
    {
        $c = $this->caseWithDecision(DecisionType::NoAction);
        $c->close($this->closer(), $this->rationale(), $this->at(4), $this->closurePolicy);
        $event = $c->releaseEvents()[4];
        self::assertTrue($c->isClosed());
        self::assertInstanceOf(ModerationCaseClosed::class, $event);
        self::assertSame($this->decisionId()->value, $event->finalDecisionId->value);
        self::assertSame($this->closer()->value, $event->closedBy->value);
    }

    private function case(): ModerationCase
    {
        return ModerationCase::open($this->caseId(), $this->listingId(), $this->reportId(), $this->reporter(), $this->reason(), $this->at(0));
    }

    private function acceptedCase(): ModerationCase
    {
        $c = $this->case();
        $c->validateReport($this->reportId(), $this->acceptedAssessment(), $this->rationale(), $this->validator(), $this->at(1), $this->validationPolicy);

        return $c;
    }

    private function caseWithFinding(): ModerationCase
    {
        $c = $this->acceptedCase();
        $c->recordFinding($this->findingId(), [$this->reportId()], FindingSeverity::High, $this->rationale(), $this->finder(), $this->at(2));

        return $c;
    }

    private function caseWithDecision(DecisionType $type): ModerationCase
    {
        $c = $this->caseWithFinding();
        $c->issueDecision($this->decisionId(), [$this->findingId()], $type, $this->rationale(), $this->decider(), null, $this->at(3), $this->decisionPolicy);

        return $c;
    }

    private function acceptedAssessment(): ReportValidationAssessment
    {
        return new ReportValidationAssessment(ReportAdmissibility::Admissible, ReportCompleteness::Complete, ReportAuthenticity::Sufficient, ReportHandlingDecision::TakeCharge);
    }

    private function dismissedAssessment(): ReportValidationAssessment
    {
        return new ReportValidationAssessment(ReportAdmissibility::Inadmissible, ReportCompleteness::Incomplete, ReportAuthenticity::Insufficient, ReportHandlingDecision::Dismiss);
    }

    private function caseId(): ModerationCaseId
    {
        return ModerationCaseId::fromString('80000000-0000-4000-8000-000000000001');
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('70000000-0000-4000-8000-000000000001');
    }

    private function reportId(): ReportId
    {
        return ReportId::fromString('80000000-0000-4000-8000-000000000002');
    }

    private function secondReportId(): ReportId
    {
        return ReportId::fromString('80000000-0000-4000-8000-000000000003');
    }

    private function findingId(): FindingId
    {
        return FindingId::fromString('80000000-0000-4000-8000-000000000004');
    }

    private function decisionId(): DecisionId
    {
        return DecisionId::fromString('80000000-0000-4000-8000-000000000005');
    }

    private function secondDecisionId(): DecisionId
    {
        return DecisionId::fromString('80000000-0000-4000-8000-000000000006');
    }

    private function reporter(): ReporterId
    {
        return ReporterId::fromString('reporter-1');
    }

    private function validator(): ModeratorId
    {
        return ModeratorId::fromString('validator-1');
    }

    private function finder(): ModeratorId
    {
        return ModeratorId::fromString('finder-1');
    }

    private function decider(): ModeratorId
    {
        return ModeratorId::fromString('decider-1');
    }

    private function secondDecider(): ModeratorId
    {
        return ModeratorId::fromString('decider-2');
    }

    private function closer(): ModeratorId
    {
        return ModeratorId::fromString('closer-1');
    }

    private function reason(): ReportReason
    {
        return ReportReason::fromString('Annonce potentiellement trompeuse');
    }

    private function rationale(): ModerationRationale
    {
        return ModerationRationale::fromString('Preuve métier vérifiée');
    }

    private function at(int $m): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00')->modify("+{$m} minutes");
    }
}
