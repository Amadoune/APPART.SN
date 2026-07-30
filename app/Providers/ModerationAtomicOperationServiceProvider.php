<?php

namespace App\Providers;

use App\Application\ModerationAtomicOperation\Contract\ModerationAtomicOperationV1;
use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationAtomicOperation\ModerationAtomicMutation;
use App\Infrastructure\ModerationAtomicOperation\PostgreSql\PostgreSqlModerationAtomicOperation;
use App\Infrastructure\ModerationAtomicOperation\PostgreSql\PostgreSqlModerationOutboxAppender;
use Illuminate\Support\ServiceProvider;

final class ModerationAtomicOperationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlModerationAtomicOperation::class);
        $this->app->alias(PostgreSqlModerationAtomicOperation::class, ModerationAtomicOperationV1::class);
        $this->app->singleton(PostgreSqlModerationOutboxAppender::class);
        $this->app->alias(PostgreSqlModerationOutboxAppender::class, ModerationOutboxAppenderV1::class);
        $this->app->singleton(ModerationAtomicMutation::class);
    }
}
