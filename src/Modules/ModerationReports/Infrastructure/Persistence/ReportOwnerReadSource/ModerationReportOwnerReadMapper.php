<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence\ReportOwnerReadSource;

use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadState;
use UnexpectedValueException;

final class ModerationReportOwnerReadMapper
{
    /** @param array<string, mixed> $row */
    public function state(array $row, string $expectedReportId): ModerationReportOwnerReadState
    {
        $caseId = $row['case_id'] ?? null;
        $reportId = $row['report_id'] ?? null;
        $reporterAccountId = $row['reporter_account_id'] ?? null;

        if (! is_string($caseId) || ! self::uuid($caseId)
            || ! is_string($reportId) || ! hash_equals($expectedReportId, $reportId)
            || ! self::uuid($reportId)
            || ! is_string($reporterAccountId) || ! self::uuid($reporterAccountId)) {
            throw new UnexpectedValueException('The moderation report owner source row is corrupted.');
        }

        return new ModerationReportOwnerReadState($caseId, $reportId, $reporterAccountId);
    }

    public static function uuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
