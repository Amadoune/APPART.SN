<?php

namespace Tests\Unit\Modules\ModerationReports;

use Appart\Modules\ModerationReports\Application\UseCase\CloseCase;
use Appart\Modules\ModerationReports\Application\UseCase\CreateReport;
use Appart\Modules\ModerationReports\Application\UseCase\IssueDecision;
use Appart\Modules\ModerationReports\Application\UseCase\RecordFinding;
use Appart\Modules\ModerationReports\Application\UseCase\ValidateReport;
use Appart\Modules\ModerationReports\Domain\Exception\ConcurrentModerationCaseModification;
use Appart\Modules\ModerationReports\Domain\Exception\ListingUnavailable;
use Appart\Modules\ModerationReports\Domain\Model\ReportValidationAssessment;
use Appart\Modules\ModerationReports\Domain\Policy\ModerationCaseClosurePolicy;
use Appart\Modules\ModerationReports\Domain\Policy\ModerationDecisionPolicy;
use Appart\Modules\ModerationReports\Domain\Policy\ReportValidationPolicy;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionType;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingSeverity;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingEligibility;
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\ModerationReports\Support\FakeListingCatalog;
use Tests\Unit\Modules\ModerationReports\Support\FakeModerationCaseRegistry;

final class ModerationUseCasesTest extends TestCase
{
    private FakeModerationCaseRegistry $registry;

    private FakeListingCatalog $listings;

    private ReportValidationPolicy $validation;

    private ModerationDecisionPolicy $decision;

    private ModerationCaseClosurePolicy $closure;

    protected function setUp(): void
    {
        $this->registry = new FakeModerationCaseRegistry;
        $this->listings = new FakeListingCatalog;
        $this->listings->set($this->listingId(), ListingEligibility::Eligible);
        $this->validation = new ReportValidationPolicy;
        $this->decision = new ModerationDecisionPolicy;
        $this->closure = new ModerationCaseClosurePolicy;
    }

    public function test_all_use_cases_save_a_coherent_case_and_clean_events(): void
    {
        $this->create();
        (new ValidateReport($this->registry, $this->validation))->execute($this->caseId(), $this->reportId(), $this->assessment(), $this->rationale(), $this->validator(), $this->at(1));
        (new RecordFinding($this->registry))->execute($this->caseId(), $this->findingId(), [$this->reportId()], FindingSeverity::High, $this->rationale(), $this->finder(), $this->at(2));
        (new IssueDecision($this->registry, $this->decision))->execute($this->caseId(), $this->decisionId(), [$this->findingId()], DecisionType::RecommendSuspension, $this->rationale(), $this->decider(), null, $this->at(3));
        (new CloseCase($this->registry, $this->closure))->execute($this->caseId(), $this->closer(), $this->rationale(), $this->at(4));
        $case = $this->registry->find($this->caseId());
        self::assertTrue($case?->isClosed());
        self::assertSame($this->decisionId()->value, $case?->currentDecision()?->id->value);
        self::assertSame([], $case?->releaseEvents());
    }

    #[DataProvider('moderatableStatuses')]
    public function test_historical_existing_listings_remain_moderatable(ListingEligibility $eligibility): void
    {
        $this->listings->set($this->listingId(), $eligibility);
        $this->create();
        self::assertNotNull($this->registry->find($this->caseId()));
    }

    public static function moderatableStatuses(): array
    {
        return [[ListingEligibility::Eligible], [ListingEligibility::Archived], [ListingEligibility::Closed], [ListingEligibility::Unavailable]];
    }

    #[DataProvider('blockedStatuses')]
    public function test_missing_or_non_moderatable_listing_is_blocked(ListingEligibility $eligibility): void
    {
        $this->listings->set($this->listingId(), $eligibility);
        $this->expectException(ListingUnavailable::class);
        $this->create();
    }

    public static function blockedStatuses(): array
    {
        return [[ListingEligibility::Missing], [ListingEligibility::NotModeratable]];
    }

    public function test_failed_finding_save_rolls_back_mutation_and_reservation(): void
    {
        $this->create();
        (new ValidateReport($this->registry, $this->validation))->execute($this->caseId(), $this->reportId(), $this->assessment(), $this->rationale(), $this->validator(), $this->at(1));
        $this->registry->failNextSave();
        try {
            (new RecordFinding($this->registry))->execute($this->caseId(), $this->findingId(), [$this->reportId()], FindingSeverity::Low, $this->rationale(), $this->finder(), $this->at(2));
            self::fail();
        } catch (ConcurrentModerationCaseModification) {
            self::assertSame([], $this->registry->find($this->caseId())?->findings());
        }(new RecordFinding($this->registry))->execute($this->caseId(), $this->findingId(), [$this->reportId()], FindingSeverity::Low, $this->rationale(), $this->finder(), $this->at(3));
        self::assertCount(1, $this->registry->find($this->caseId())?->findings() ?? []);
    }

    public function test_failed_decision_save_rolls_back_current_decision(): void
    {
        $this->prepareFinding();
        $this->registry->failNextSave();
        try {
            (new IssueDecision($this->registry, $this->decision))->execute($this->caseId(), $this->decisionId(), [$this->findingId()], DecisionType::NoAction, $this->rationale(), $this->decider(), null, $this->at(3));
            self::fail();
        } catch (ConcurrentModerationCaseModification) {
            self::assertNull($this->registry->find($this->caseId())?->currentDecision());
        }
    }

    private function prepareFinding(): void
    {
        $this->create();
        (new ValidateReport($this->registry, $this->validation))->execute($this->caseId(), $this->reportId(), $this->assessment(), $this->rationale(), $this->validator(), $this->at(1));
        (new RecordFinding($this->registry))->execute($this->caseId(), $this->findingId(), [$this->reportId()], FindingSeverity::Low, $this->rationale(), $this->finder(), $this->at(2));
    }

    private function create(): void
    {
        (new CreateReport($this->registry, $this->listings))->execute($this->caseId(), $this->listingId(), $this->reportId(), ReporterId::fromString('reporter-1'), ReportReason::fromString('Annonce trompeuse'), $this->at(0));
    }

    private function assessment(): ReportValidationAssessment
    {
        return new ReportValidationAssessment(ReportAdmissibility::Admissible, ReportCompleteness::Complete, ReportAuthenticity::Sufficient, ReportHandlingDecision::TakeCharge);
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

    private function findingId(): FindingId
    {
        return FindingId::fromString('80000000-0000-4000-8000-000000000003');
    }

    private function decisionId(): DecisionId
    {
        return DecisionId::fromString('80000000-0000-4000-8000-000000000004');
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

    private function closer(): ModeratorId
    {
        return ModeratorId::fromString('closer-1');
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
