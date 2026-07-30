<?php

namespace App\Providers;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalVerificationStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalVerificationStore;
use Illuminate\Support\ServiceProvider;

final class ProfessionalVerificationRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlProfessionalVerificationStore::class);
        $this->app->alias(PostgreSqlProfessionalVerificationStore::class, ProfessionalVerificationStore::class);
    }
}
