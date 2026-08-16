<?php

namespace App\Providers;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceRepository;
use Illuminate\Support\ServiceProvider;

final class GeographyPlacePersistenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlPlaceRepository::class);
        $this->app->alias(PostgreSqlPlaceRepository::class, PlaceRegistry::class);
    }
}
