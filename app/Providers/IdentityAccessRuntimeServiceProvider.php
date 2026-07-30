<?php

namespace App\Providers;

use Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract\AccountAvailabilityInspector;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract\AccountClosureStateReader;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\DeterministicAccountAvailabilityInspector;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract\AccountStatusWorkflowStore;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\Contract\IdentityAccessRuntimeHealthInspector;
use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\DeterministicIdentityAccessRuntimeHealthInspector;
use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\IdentityAccessRuntimeComponent;
use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\IdentityAccessRuntimeRegistration;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountClosureStateReader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class IdentityAccessRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlAccountClosureStateReader::class);
        $this->app->alias(PostgreSqlAccountClosureStateReader::class, AccountClosureStateReader::class);

        $this->app->singleton(DeterministicAccountAvailabilityInspector::class);
        $this->app->alias(DeterministicAccountAvailabilityInspector::class, AccountAvailabilityInspector::class);

        $this->app->singleton(
            IdentityAccessRuntimeHealthInspector::class,
            static fn (Application $app): IdentityAccessRuntimeHealthInspector => new DeterministicIdentityAccessRuntimeHealthInspector([
                self::registration($app, IdentityAccessRuntimeComponent::AccountRegistry, AccountRegistry::class),
                self::registration($app, IdentityAccessRuntimeComponent::AccountStatusReader, AccountStatusWorkflowStore::class),
                self::registration($app, IdentityAccessRuntimeComponent::AccountClosureReader, AccountClosureStateReader::class),
                self::registration($app, IdentityAccessRuntimeComponent::AccountAvailability, AccountAvailabilityInspector::class),
            ]),
        );
    }

    private static function registration(
        Application $app,
        IdentityAccessRuntimeComponent $component,
        string $contract,
    ): IdentityAccessRuntimeRegistration {
        if (! $app->bound($contract)) {
            return new IdentityAccessRuntimeRegistration($component, false, false);
        }

        return new IdentityAccessRuntimeRegistration(
            $component,
            true,
            $app->make($contract) instanceof $contract,
        );
    }
}
