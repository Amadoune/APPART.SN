<?php

namespace App\Providers;

use Appart\Modules\RealEstateCatalog\Application\BusinessYear\Contract\BusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\UtcCalendarBusinessYearAuthorityV1;
use Illuminate\Support\ServiceProvider;

final class BusinessYearAuthorityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UtcCalendarBusinessYearAuthorityV1::class);
        $this->app->alias(UtcCalendarBusinessYearAuthorityV1::class, BusinessYearAuthorityV1::class);
    }
}
