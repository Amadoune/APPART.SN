<?php

namespace App\Providers;

use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAssetStore;
use Illuminate\Support\ServiceProvider;

final class MediaAssetRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlMediaAssetStore::class);
        $this->app->alias(PostgreSqlMediaAssetStore::class, MediaAssetStore::class);
    }
}
