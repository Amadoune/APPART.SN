<?php

namespace Tests\Feature;

use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract\ModerationReportOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationReportOwnerReadSourceV1;
use PDO;
use Tests\TestCase;

final class ModerationReportOwnerReadSourceRuntimeTest extends TestCase
{
    public function test_container_resolves_the_unique_owner_source_as_a_singleton(): void
    {
        $this->app->instance(PDO::class, $this->createStub(PDO::class));

        $first = $this->app->make(ModerationReportOwnerReadSourceV1::class);
        $second = $this->app->make(ModerationReportOwnerReadSourceV1::class);

        self::assertInstanceOf(PostgreSqlModerationReportOwnerReadSourceV1::class, $first);
        self::assertSame($first, $second);
    }
}
