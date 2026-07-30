<?php

namespace Tests\Unit\ModerationReportOwnerReadSource;

use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadResult;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadState;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadStatus;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ReportOwnerReadSource\ModerationReportOwnerReadMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class ModerationReportOwnerReadSourceContractTest extends TestCase
{
    #[Test]
    public function results_are_closed_and_expose_state_only_when_found(): void
    {
        $state = new ModerationReportOwnerReadState($this->id(1), $this->id(2), $this->id(3));

        self::assertSame(ModerationReportOwnerReadStatus::Found, ModerationReportOwnerReadResult::found($state)->status);
        self::assertSame($state, ModerationReportOwnerReadResult::found($state)->state);
        self::assertSame(ModerationReportOwnerReadStatus::Missing, ModerationReportOwnerReadResult::missing()->status);
        self::assertNull(ModerationReportOwnerReadResult::missing()->state);
        self::assertSame(ModerationReportOwnerReadStatus::Corrupted, ModerationReportOwnerReadResult::corrupted()->status);
        self::assertSame(ModerationReportOwnerReadStatus::DependencyUnavailable, ModerationReportOwnerReadResult::dependencyUnavailable()->status);
    }

    #[Test]
    public function mapper_accepts_only_the_exact_report_and_minimal_valid_identities(): void
    {
        $mapper = new ModerationReportOwnerReadMapper;
        $state = $mapper->state([
            'case_id' => $this->id(1),
            'report_id' => $this->id(2),
            'reporter_account_id' => $this->id(3),
            'ignored_payload' => 'not exposed',
        ], $this->id(2));

        self::assertSame($this->id(1), $state->caseId);
        self::assertSame($this->id(2), $state->reportId);
        self::assertSame($this->id(3), $state->reporterAccountId);

        $this->expectException(UnexpectedValueException::class);
        $mapper->state([
            'case_id' => $this->id(1),
            'report_id' => $this->id(4),
            'reporter_account_id' => $this->id(3),
        ], $this->id(2));
    }

    private function id(int $suffix): string
    {
        return sprintf('53a10000-0000-4000-8000-%012d', $suffix);
    }
}
