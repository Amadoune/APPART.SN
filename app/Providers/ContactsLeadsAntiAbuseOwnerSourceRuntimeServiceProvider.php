<?php

namespace App\Providers;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\Contract\AntiAbuseOwnerSource;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\Contract\AntiAbuseOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\Contract\AntiAbuseOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\DeterministicAntiAbuseOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\AntiAbuseOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\AntiAbuseOwnerSourceConnection;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlAntiAbuseOwnerSource;
use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class ContactsLeadsAntiAbuseOwnerSourceRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AntiAbuseOwnerSourceMapper::class);
        $this->app->singleton(
            AntiAbuseOwnerSourceConnection::class,
            static function ($app): AntiAbuseOwnerSourceConnection {
                $configuration = $app->make(DatabaseManager::class)
                    ->connection('pgsql')
                    ->getConfig();

                return new AntiAbuseOwnerSourceConnection(
                    (new PostgresConnector)->connect($configuration),
                );
            },
        );
        $this->app->singleton(
            PostgreSqlAntiAbuseOwnerSource::class,
            static fn ($app): PostgreSqlAntiAbuseOwnerSource => new PostgreSqlAntiAbuseOwnerSource(
                $app->make(AntiAbuseOwnerSourceConnection::class)->connection,
                $app->make(AntiAbuseOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlAntiAbuseOwnerSource::class, AntiAbuseOwnerSource::class);
        $this->app->singleton(
            DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy => new DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy(
                $app->make(AntiAbuseOwnerSource::class),
            ),
        );
        $this->app->alias(
            DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy::class,
            AntiAbuseOwnerSourceRuntimeAvailabilityPolicy::class,
        );
        $this->app->singleton(DeterministicAntiAbuseOwnerSourceRuntimeV1::class);
        $this->app->alias(DeterministicAntiAbuseOwnerSourceRuntimeV1::class, AntiAbuseOwnerSourceRuntimeV1::class);
    }
}
