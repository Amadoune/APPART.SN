<?php

namespace App\Providers;

use App\Application\ModerationRuntime\Contract\ModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeV1;
use App\Application\ModerationRuntime\ModerationRuntimeHealthInspector;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationDecisionStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationQueueStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueStore;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class ModerationRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModerationPersistenceMapper::class);
        $this->app->singleton(PostgreSqlModerationCaseStore::class);
        $this->app->alias(PostgreSqlModerationCaseStore::class, ModerationCaseStore::class);
        $this->app->singleton(PostgreSqlModerationDecisionStore::class);
        $this->app->alias(PostgreSqlModerationDecisionStore::class, ModerationDecisionStore::class);
        $this->app->singleton(PostgreSqlModerationQueueStore::class);
        $this->app->alias(PostgreSqlModerationQueueStore::class, ModerationQueueStore::class);

        $this->app->singleton(DeterministicModerationQueueRuntimeV1::class);
        $this->app->alias(DeterministicModerationQueueRuntimeV1::class, ModerationQueueRuntimeV1::class);
        $this->app->singleton(
            ModerationRuntimeAvailabilityPolicy::class,
            static fn (Application $app): ModerationRuntimeAvailabilityPolicy => new DeterministicModerationRuntimeAvailabilityPolicy([
                'case_store' => self::compatible($app, ModerationCaseStore::class),
                'decision_store' => self::compatible($app, ModerationDecisionStore::class),
                'queue_store' => self::compatible($app, ModerationQueueStore::class),
            ]),
        );
        $this->app->singleton(DeterministicModerationRuntimeV1::class);
        $this->app->alias(DeterministicModerationRuntimeV1::class, ModerationRuntimeV1::class);

        $this->app->extend(
            RuntimeHealthInspector::class,
            static fn (RuntimeHealthInspector $baseline, Application $app): RuntimeHealthInspector => new ModerationRuntimeHealthInspector(
                $baseline,
                $app->bound(ModerationRuntimeV1::class),
                $app->make(ModerationRuntimeV1::class),
                $app->bound(ModerationQueueRuntimeV1::class),
                $app->make(ModerationQueueRuntimeV1::class),
            ),
        );
    }

    private static function compatible(Application $app, string $contract): bool
    {
        return $app->bound($contract) && $app->make($contract) instanceof $contract;
    }
}
