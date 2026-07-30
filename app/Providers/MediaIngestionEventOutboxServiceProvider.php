<?php

namespace App\Providers;

use App\Application\MediaIngestionEventIntegration\MediaIngestionAtomicDelivery;
use App\Application\MediaIngestionEventOutbox\Contract\MediaIngestionOutboxReader;
use App\Application\MediaIngestionEventOutbox\Contract\MediaIngestionOutboxWriter;
use App\Infrastructure\MediaIngestionEventOutbox\PostgreSql\PostgreSqlMediaIngestionAtomicDeliveryTransaction;
use App\Infrastructure\MediaIngestionEventOutbox\PostgreSql\PostgreSqlMediaIngestionOutbox;
use Appart\Modules\Media\Application\MediaIngestionEventIntegration\Contract\MediaIngestionAtomicDeliveryTransaction;
use Illuminate\Support\ServiceProvider;

final class MediaIngestionEventOutboxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlMediaIngestionOutbox::class);
        $this->app->alias(PostgreSqlMediaIngestionOutbox::class, MediaIngestionOutboxWriter::class);
        $this->app->alias(PostgreSqlMediaIngestionOutbox::class, MediaIngestionOutboxReader::class);
        $this->app->singleton(PostgreSqlMediaIngestionAtomicDeliveryTransaction::class);
        $this->app->alias(
            PostgreSqlMediaIngestionAtomicDeliveryTransaction::class,
            MediaIngestionAtomicDeliveryTransaction::class,
        );
        $this->app->singleton(MediaIngestionAtomicDelivery::class);
    }
}
