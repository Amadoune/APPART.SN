<?php

namespace App\Providers;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract\ReservationAvailabilityOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract\ReservationAvailabilityOwnerSourceRuntimeReadV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1;
use Illuminate\Support\ServiceProvider;

final class ReservationAvailabilityOwnerSourceRuntimeReadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy::class);
        $this->app->alias(
            DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy::class,
            ReservationAvailabilityOwnerSourceRuntimeReadPolicy::class,
        );
        $this->app->singleton(DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1::class);
        $this->app->alias(
            DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1::class,
            ReservationAvailabilityOwnerSourceRuntimeReadV1::class,
        );
    }
}
