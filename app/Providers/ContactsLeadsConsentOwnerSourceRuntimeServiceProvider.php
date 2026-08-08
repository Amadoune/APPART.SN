<?php

namespace App\Providers;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\Contract\ConsentOwnerSource;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\Contract\ConsentOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\Contract\ConsentOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\DeterministicConsentOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\ConsentOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\ConsentOwnerSourceConnection;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlConsentOwnerSource;
use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class ContactsLeadsConsentOwnerSourceRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConsentOwnerSourceMapper::class);
        $this->app->singleton(
            ConsentOwnerSourceConnection::class,
            static function ($app): ConsentOwnerSourceConnection {
                $configuration = $app->make(DatabaseManager::class)
                    ->connection('pgsql')
                    ->getConfig();

                return new ConsentOwnerSourceConnection(
                    (new PostgresConnector)->connect($configuration),
                );
            },
        );
        $this->app->singleton(
            PostgreSqlConsentOwnerSource::class,
            static fn ($app): PostgreSqlConsentOwnerSource => new PostgreSqlConsentOwnerSource(
                $app->make(ConsentOwnerSourceConnection::class)->connection,
                $app->make(ConsentOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlConsentOwnerSource::class, ConsentOwnerSource::class);
        $this->app->singleton(
            DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy => new DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy(
                $app->make(ConsentOwnerSource::class),
            ),
        );
        $this->app->alias(
            DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy::class,
            ConsentOwnerSourceRuntimeAvailabilityPolicy::class,
        );
        $this->app->singleton(DeterministicConsentOwnerSourceRuntimeV1::class);
        $this->app->alias(DeterministicConsentOwnerSourceRuntimeV1::class, ConsentOwnerSourceRuntimeV1::class);
    }
}
