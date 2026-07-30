<?php

namespace Tests\Unit\ModerationHttp;

use App\Application\ModerationHttp\ModerationHttpResult;
use App\Application\ModerationHttp\ModerationHttpStatus;
use App\Http\ModerationHttpResponseMapper;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\OwnModerationReportViewV1;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ModerationHttpResponseMapperTest extends TestCase
{
    public function test_closed_statuses_and_nested_dtos_are_mapped_deterministically(): void
    {
        $mapper = new ModerationHttpResponseMapper;
        $mapped = $mapper->map(new ModerationHttpResult(ModerationHttpStatus::Succeeded, [
            'report' => new OwnModerationReportViewV1(
                'report',
                'Open',
                new DateTimeImmutable('2026-07-30T10:00:00+00:00'),
            ),
        ]));

        self::assertSame(200, $mapped['status']);
        self::assertSame('succeeded', $mapped['body']['status']);
        self::assertSame('report', $mapped['body']['report']['reportId']);
        self::assertSame('2026-07-30T10:00:00.000000+00:00', $mapped['body']['report']['updatedAt']);
    }

    public function test_every_internal_status_has_a_closed_http_code(): void
    {
        $mapper = new ModerationHttpResponseMapper;
        $codes = [];
        foreach (ModerationHttpStatus::cases() as $status) {
            $codes[$status->value] = $mapper->map(new ModerationHttpResult($status))['status'];
        }

        self::assertSame([
            'succeeded' => 200,
            'created' => 201,
            'not_found' => 404,
            'forbidden' => 403,
            'conflict' => 409,
            'invalid' => 422,
            'unavailable' => 503,
        ], $codes);
    }
}
