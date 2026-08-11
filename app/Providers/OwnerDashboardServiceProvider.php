<?php

namespace App\Providers;

use App\Application\OwnerDashboard\Contract\OwnerDashboardReadSourceV1;
use App\Application\OwnerDashboard\PublicProjectionOwnerDashboardReadSource;
use Illuminate\Support\ServiceProvider;

final class OwnerDashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PublicProjectionOwnerDashboardReadSource::class);
        $this->app->alias(PublicProjectionOwnerDashboardReadSource::class, OwnerDashboardReadSourceV1::class);
    }
}
