<?php

namespace App\Providers;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerReader\OwnerReservationAvailabilityReaderV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\Contract\ReservationAvailabilityReaderV1;
use Illuminate\Support\ServiceProvider;

final class ReservationAvailabilityOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OwnerReservationAvailabilityReaderV1::class);
        $this->app->alias(OwnerReservationAvailabilityReaderV1::class, ReservationAvailabilityReaderV1::class);
    }
}
