<?php

namespace App\Providers;

use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\Runtime\DeterministicSecurityComplianceRuntime;
use Appart\Modules\SecurityCompliance\Application\Runtime\DeterministicSecurityComplianceRuntimeAvailabilityPolicy;
use Appart\Modules\SecurityCompliance\Application\Runtime\SecurityComplianceRuntimeAvailabilityPolicy;
use Appart\Modules\SecurityCompliance\Application\Runtime\SecurityComplianceRuntimeV1;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\PostgreSql\PostgreSqlSecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\SecurityComplianceOwnerSourceMapper;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class SecurityComplianceRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SecurityComplianceOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlSecurityComplianceOwnerSource::class,
            static fn ($app): PostgreSqlSecurityComplianceOwnerSource => new PostgreSqlSecurityComplianceOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(SecurityComplianceOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlSecurityComplianceOwnerSource::class, SecurityComplianceOwnerSource::class);
        $this->app->singleton(
            DeterministicSecurityComplianceRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicSecurityComplianceRuntimeAvailabilityPolicy => new DeterministicSecurityComplianceRuntimeAvailabilityPolicy(
                $app->make(SecurityComplianceOwnerSource::class),
            ),
        );
        $this->app->alias(DeterministicSecurityComplianceRuntimeAvailabilityPolicy::class, SecurityComplianceRuntimeAvailabilityPolicy::class);
        $this->app->singleton(DeterministicSecurityComplianceRuntime::class);
        $this->app->alias(DeterministicSecurityComplianceRuntime::class, SecurityComplianceRuntimeV1::class);
    }
}
