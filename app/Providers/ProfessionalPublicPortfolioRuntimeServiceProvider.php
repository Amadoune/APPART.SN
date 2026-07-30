<?php

namespace App\Providers;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicPortfolioStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalPublicPortfolioStore;
use Illuminate\Support\ServiceProvider;

final class ProfessionalPublicPortfolioRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlProfessionalPublicPortfolioStore::class);
        $this->app->alias(PostgreSqlProfessionalPublicPortfolioStore::class, ProfessionalPublicPortfolioStore::class);
    }
}
