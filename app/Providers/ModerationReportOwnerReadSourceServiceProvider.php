<?php

namespace App\Providers;

use App\Application\ModerationReportOwnerReadSource\CanonicalModerationReportCaseIdentityV1;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract\ModerationReportCaseIdentityV1;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract\ModerationReportOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationReportOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ReportOwnerReadSource\ModerationReportOwnerReadMapper;
use Illuminate\Support\ServiceProvider;

final class ModerationReportOwnerReadSourceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModerationReportOwnerReadMapper::class);
        $this->app->singleton(CanonicalModerationReportCaseIdentityV1::class);
        $this->app->alias(
            CanonicalModerationReportCaseIdentityV1::class,
            ModerationReportCaseIdentityV1::class,
        );
        $this->app->singleton(PostgreSqlModerationReportOwnerReadSourceV1::class);
        $this->app->alias(
            PostgreSqlModerationReportOwnerReadSourceV1::class,
            ModerationReportOwnerReadSourceV1::class,
        );
    }
}
