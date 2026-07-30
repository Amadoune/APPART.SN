<?php

namespace App\Providers;

use App\Application\ModerationListingHandoff\Contract\ModerationListingHandoffResultStore;
use App\Application\ModerationListingHandoff\ModerationListingHandoffConsumer;
use App\Application\ModerationListingHandoff\ModerationListingHandoffTerminalIntegrator;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationListingHandoffResultStore;
use Illuminate\Support\ServiceProvider;

final class ModerationListingHandoffServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlModerationListingHandoffResultStore::class);
        $this->app->alias(
            PostgreSqlModerationListingHandoffResultStore::class,
            ModerationListingHandoffResultStore::class,
        );
        $this->app->singleton(ModerationListingHandoffConsumer::class);
        $this->app->singleton(ModerationListingHandoffTerminalIntegrator::class);
    }
}
