<?php

namespace App\Providers;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\Contract\ReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\Contract\ReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\Contract\ReservationAvailabilityOwnerSourceRuntimeV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\DeterministicReservationAvailabilityOwnerSourceRuntimeV1;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class ReservationAvailabilityOwnerSourceRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReservationAvailabilityOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlReservationAvailabilityOwnerSource::class,
            static fn ($app): PostgreSqlReservationAvailabilityOwnerSource => new PostgreSqlReservationAvailabilityOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(ReservationAvailabilityOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlReservationAvailabilityOwnerSource::class, ReservationAvailabilityOwnerSource::class);
        $this->app->singleton(
            DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy => new DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy(
                $app->make(ReservationAvailabilityOwnerSource::class),
            ),
        );
        $this->app->alias(
            DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy::class,
            ReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy::class,
        );
        $this->app->singleton(DeterministicReservationAvailabilityOwnerSourceRuntimeV1::class);
        $this->app->alias(
            DeterministicReservationAvailabilityOwnerSourceRuntimeV1::class,
            ReservationAvailabilityOwnerSourceRuntimeV1::class,
        );
    }
}
