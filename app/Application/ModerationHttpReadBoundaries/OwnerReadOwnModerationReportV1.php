<?php

namespace App\Application\ModerationHttpReadBoundaries;

use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadOwnModerationReportV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadOwnModerationReportQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadOwnModerationReportResultV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceReadStatus;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract\ModerationReportOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadStatus;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\OwnModerationReportViewMapperV1;

final readonly class OwnerReadOwnModerationReportV1 implements ReadOwnModerationReportV1
{
    public function __construct(
        private ModerationReportOwnerReadSourceV1 $reports,
        private ModerationCaseStore $cases,
        private OwnModerationReportViewMapperV1 $mapper,
    ) {}

    public function read(ReadOwnModerationReportQueryV1 $query): ReadOwnModerationReportResultV1
    {
        $report = $this->reports->resolve($query->reportId);
        if ($report->status === ModerationReportOwnerReadStatus::DependencyUnavailable) {
            return ReadOwnModerationReportResultV1::dependencyUnavailable();
        }
        if (
            $report->status !== ModerationReportOwnerReadStatus::Found
            || $report->state === null
            || ! hash_equals($report->state->reporterAccountId, $query->actorAccountId)
        ) {
            return ReadOwnModerationReportResultV1::notVisible();
        }

        $case = $this->cases->read($report->state->caseId);
        if ($case->status === ModerationPersistenceReadStatus::DependencyUnavailable) {
            return ReadOwnModerationReportResultV1::dependencyUnavailable();
        }
        if ($case->status !== ModerationPersistenceReadStatus::Found || $case->state === null) {
            return ReadOwnModerationReportResultV1::notVisible();
        }

        return ReadOwnModerationReportResultV1::visible(
            $this->mapper->map($query->reportId, $case->state),
        );
    }
}
