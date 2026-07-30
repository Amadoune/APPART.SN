<?php

namespace App\Providers;

use App\Application\ModerationOperationalAudit\Contract\ModerationOperationalAuditDeliveryReaderV1;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditConsumer;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditRecordFactory;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOperationalAuditDeliveryReader;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\Contract\AdministrationAuditAppendV1;
use Illuminate\Support\ServiceProvider;

final class ModerationOperationalAuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModerationOperationalAuditRecordFactory::class);
        $this->app->singleton(PostgreSqlModerationOperationalAuditDeliveryReader::class);
        $this->app->alias(
            PostgreSqlModerationOperationalAuditDeliveryReader::class,
            ModerationOperationalAuditDeliveryReaderV1::class,
        );
        $this->app->singleton(
            ModerationOperationalAuditConsumer::class,
            fn ($app): ModerationOperationalAuditConsumer => new ModerationOperationalAuditConsumer(
                $app->make(ModerationOperationalAuditDeliveryReaderV1::class),
                $app->make(AdministrationAuditAppendV1::class),
                $app->make(ModerationOperationalAuditRecordFactory::class),
            ),
        );
    }
}
