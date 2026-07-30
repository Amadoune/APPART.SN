<?php

namespace App\Providers;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalPublicProfileStore;
use Illuminate\Support\ServiceProvider;

final class ProfessionalPublicProfileRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlProfessionalPublicProfileStore::class);
        $this->app->alias(PostgreSqlProfessionalPublicProfileStore::class, ProfessionalPublicProfileStore::class);
    }
}
