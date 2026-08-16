<?php

namespace App\Providers;

use Appart\Modules\RealEstateCatalog\Application\Contract\GeographicPlaceCatalog;
use Appart\Modules\RealEstateCatalog\Domain\Policy\GeographicPlaceAddressabilityPolicy;
use Appart\Modules\RealEstateCatalog\Infrastructure\Geography\GeographyBackedGeographicPlaceCatalog;
use Illuminate\Support\ServiceProvider;

final class GeographicPlaceCatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GeographicPlaceAddressabilityPolicy::class);
        $this->app->singleton(GeographyBackedGeographicPlaceCatalog::class);
        $this->app->alias(GeographyBackedGeographicPlaceCatalog::class, GeographicPlaceCatalog::class);
    }
}
