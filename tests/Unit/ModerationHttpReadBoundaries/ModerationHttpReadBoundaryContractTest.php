<?php

namespace Tests\Unit\ModerationHttpReadBoundaries;

use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationCaseViewV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationDecisionViewV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\OwnModerationReportViewV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationCaseResultV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationDecisionResultV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationQueueResultV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadOwnModerationReportResultV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadStatusV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadPageV1;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ModerationHttpReadBoundaryContractTest extends TestCase
{
    public function test_all_results_are_closed_typed_and_carry_only_their_minimal_view(): void
    {
        $at = new DateTimeImmutable('2026-07-30T10:00:00+00:00');
        $report = ReadOwnModerationReportResultV1::visible(
            new OwnModerationReportViewV1('report', 'Open', $at),
        );
        $queue = ReadModerationQueueResultV1::available(new ModerationQueueReadPageV1([], null));
        $case = ReadModerationCaseResultV1::found(
            new ModerationCaseViewV1('case', 'Listing', 'target', 'Open', null, 1, $at),
        );
        $decision = ReadModerationDecisionResultV1::found(
            new ModerationDecisionViewV1('decision', ['disposition' => 'Suspend'], $at),
        );

        self::assertSame(ReadStatusV1::Visible, $report->status);
        self::assertSame(ReadStatusV1::Available, $queue->status);
        self::assertSame(ReadStatusV1::Found, $case->status);
        self::assertSame(ReadStatusV1::Found, $decision->status);
        self::assertCount(11, ReadStatusV1::cases());
    }
}
