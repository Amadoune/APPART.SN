<?php

namespace App\Providers;

use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyAuthoringStore;
use Illuminate\Support\ServiceProvider;

final class PropertyAuthoringRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlPropertyAuthoringStore::class);
        $this->app->alias(PostgreSqlPropertyAuthoringStore::class, PropertyAuthoringStore::class);
    }
}
