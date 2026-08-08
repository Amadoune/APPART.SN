<?php

namespace App\Providers;

use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\Runtime\DeterministicNotificationsRuntime;
use Appart\Modules\Notifications\Application\Runtime\DeterministicNotificationsRuntimeAvailabilityPolicy;
use Appart\Modules\Notifications\Application\Runtime\NotificationsRuntimeAvailabilityPolicy;
use Appart\Modules\Notifications\Application\Runtime\NotificationsRuntimeV1;
use Appart\Modules\Notifications\Infrastructure\Persistence\NotificationsOwnerSourceMapper;
use Appart\Modules\Notifications\Infrastructure\Persistence\PostgreSql\PostgreSqlNotificationsOwnerSource;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class NotificationsRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationsOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlNotificationsOwnerSource::class,
            static fn ($app): PostgreSqlNotificationsOwnerSource => new PostgreSqlNotificationsOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(NotificationsOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlNotificationsOwnerSource::class, NotificationsOwnerSource::class);
        $this->app->singleton(
            DeterministicNotificationsRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicNotificationsRuntimeAvailabilityPolicy => new DeterministicNotificationsRuntimeAvailabilityPolicy(
                $app->make(NotificationsOwnerSource::class),
            ),
        );
        $this->app->alias(DeterministicNotificationsRuntimeAvailabilityPolicy::class, NotificationsRuntimeAvailabilityPolicy::class);
        $this->app->singleton(DeterministicNotificationsRuntime::class);
        $this->app->alias(DeterministicNotificationsRuntime::class, NotificationsRuntimeV1::class);
    }
}
