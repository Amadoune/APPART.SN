<?php

namespace App\Providers;

use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationEventOutbox\Contract\ModerationOutboxReaderV1;
use App\Application\ModerationOperationalAuditEventProduction\Contract\ModerationOperationalAuditOutboxAppenderV1;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOperationalAuditOutboxAppender;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOutbox;
use Illuminate\Support\ServiceProvider;

final class ModerationEventOutboxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlModerationOutbox::class);
        $this->app->alias(PostgreSqlModerationOutbox::class, ModerationOutboxAppenderV1::class);
        $this->app->alias(PostgreSqlModerationOutbox::class, ModerationOutboxReaderV1::class);
        $this->app->singleton(PostgreSqlModerationOperationalAuditOutboxAppender::class);
        $this->app->alias(
            PostgreSqlModerationOperationalAuditOutboxAppender::class,
            ModerationOperationalAuditOutboxAppenderV1::class,
        );
    }
}
