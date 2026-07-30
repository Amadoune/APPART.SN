<?php

namespace App\Providers;

use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationCaseV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationDecisionV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationQueueV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadOwnModerationReportV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationCaseV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationDecisionV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationQueueV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadOwnModerationReportV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\Contract\ModerationQueueOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueCursorCodecV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueReadMapperV1;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\ModerationCaseViewMapperV1;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\ModerationDecisionViewMapperV1;
use Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries\OwnModerationReportViewMapperV1;
use Illuminate\Support\ServiceProvider;

final class ModerationHttpReadBoundariesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModerationQueueReadMapperV1::class);
        $this->app->singleton(
            ModerationQueueCursorCodecV1::class,
            static fn (): ModerationQueueCursorCodecV1 => new ModerationQueueCursorCodecV1(
                (string) config('app.key'),
            ),
        );
        $this->app->singleton(PostgreSqlModerationQueueOwnerReadSourceV1::class);
        $this->app->alias(
            PostgreSqlModerationQueueOwnerReadSourceV1::class,
            ModerationQueueOwnerReadSourceV1::class,
        );

        foreach ([
            OwnModerationReportViewMapperV1::class,
            ModerationCaseViewMapperV1::class,
            ModerationDecisionViewMapperV1::class,
        ] as $mapper) {
            $this->app->singleton($mapper);
        }

        $this->bindReader(OwnerReadOwnModerationReportV1::class, ReadOwnModerationReportV1::class);
        $this->bindReader(OwnerReadModerationQueueV1::class, ReadModerationQueueV1::class);
        $this->bindReader(OwnerReadModerationCaseV1::class, ReadModerationCaseV1::class);
        $this->bindReader(OwnerReadModerationDecisionV1::class, ReadModerationDecisionV1::class);
    }

    /** @param class-string $implementation @param class-string $contract */
    private function bindReader(string $implementation, string $contract): void
    {
        $this->app->singleton($implementation);
        $this->app->alias($implementation, $contract);
    }
}
