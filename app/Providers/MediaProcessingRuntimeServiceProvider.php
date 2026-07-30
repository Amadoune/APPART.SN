<?php

namespace App\Providers;

use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaProcessingStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaProcessingStore;
use Illuminate\Support\ServiceProvider;

final class MediaProcessingRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlMediaProcessingStore::class);
        $this->app->alias(PostgreSqlMediaProcessingStore::class, MediaProcessingStore::class);
    }
}
