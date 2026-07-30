<?php

namespace Tests\Unit\ModerationHttpReadBoundaries;

use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationQueueV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadOwnModerationReportV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationQueueQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadOwnModerationReportQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadStatusV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\Contract\ModerationQueueOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadFilterV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadStateV1;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract\ModerationReportOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadResult;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadState;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\OwnModerationReportViewMapperV1;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ModerationHttpReadBoundaryPolicyTest extends TestCase
{
    public function test_own_report_is_anti_enumeration_and_never_reads_case_for_another_actor(): void
    {
        $reports = $this->createMock(ModerationReportOwnerReadSourceV1::class);
        $reports->method('resolve')->willReturn(ModerationReportOwnerReadResult::found(
            new ModerationReportOwnerReadState('case', 'report', 'owner'),
        ));
        $cases = $this->createMock(ModerationCaseStore::class);
        $cases->expects(self::never())->method('read');

        $result = (new OwnerReadOwnModerationReportV1(
            $reports,
            $cases,
            new OwnModerationReportViewMapperV1,
        ))->read(new ReadOwnModerationReportQueryV1('report', 'other'));

        self::assertSame(ReadStatusV1::NotVisible, $result->status);
        self::assertNull($result->report);
    }

    public function test_queue_is_fail_closed_before_the_owner_source_is_read(): void
    {
        $authorization = $this->createMock(ModeratorAuthorizationReaderV1::class);
        $authorization->method('authorize')->willReturn(ModeratorAuthorizationDecisionV1::Denied);
        $queue = $this->createMock(ModerationQueueOwnerReadSourceV1::class);
        $queue->expects(self::never())->method('read');

        $result = (new OwnerReadModerationQueueV1($authorization, $queue))->read(
            new ReadModerationQueueQueryV1(
                '00000000-0000-4000-8000-000000000001',
                new DateTimeImmutable('2026-07-30T10:00:00+00:00'),
                new ModerationQueueReadFilterV1(ModerationQueueReadStateV1::Available),
                null,
                25,
            ),
        );

        self::assertSame(ReadStatusV1::ForbiddenActor, $result->status);
        self::assertNull($result->page);
    }
}
