<?php

namespace App\Providers;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\DeterministicExperienceAcceptanceRuntime;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\ExperienceAcceptanceRuntimeAvailabilityPolicy;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\ExperienceAcceptanceRuntimeV1;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\ExperienceAcceptanceOwnerSourceMapper;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\PostgreSql\PostgreSqlExperienceAcceptanceOwnerSource;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class ExperienceAcceptanceRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ExperienceAcceptanceOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlExperienceAcceptanceOwnerSource::class,
            static fn ($app): PostgreSqlExperienceAcceptanceOwnerSource => new PostgreSqlExperienceAcceptanceOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(ExperienceAcceptanceOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlExperienceAcceptanceOwnerSource::class, ExperienceAcceptanceOwnerSource::class);
        $this->app->singleton(
            DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy => new DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy(
                $app->make(ExperienceAcceptanceOwnerSource::class),
            ),
        );
        $this->app->alias(DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy::class, ExperienceAcceptanceRuntimeAvailabilityPolicy::class);
        $this->app->singleton(DeterministicExperienceAcceptanceRuntime::class);
        $this->app->alias(DeterministicExperienceAcceptanceRuntime::class, ExperienceAcceptanceRuntimeV1::class);
    }
}
