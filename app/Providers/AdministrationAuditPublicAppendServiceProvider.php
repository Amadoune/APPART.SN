<?php

namespace App\Providers;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\Contract\AdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\AdministrationAuditAppendConnection;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PublicAuditAppend\AdministrationAuditAppendMapperV1;
use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class AdministrationAuditPublicAppendServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdministrationAuditAppendMapperV1::class);
        $this->app->singleton(
            AdministrationAuditAppendConnection::class,
            static function ($app): AdministrationAuditAppendConnection {
                $configuration = $app->make(DatabaseManager::class)
                    ->connection('pgsql')
                    ->getConfig();

                return new AdministrationAuditAppendConnection(
                    (new PostgresConnector)->connect($configuration),
                );
            },
        );
        $this->app->singleton(PostgreSqlAdministrationAuditAppendV1::class);
        $this->app->alias(
            PostgreSqlAdministrationAuditAppendV1::class,
            AdministrationAuditAppendV1::class,
        );
    }
}
