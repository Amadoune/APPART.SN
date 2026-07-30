<?php

namespace App\Providers;

use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaUploadStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaUploadStore;
use Illuminate\Support\ServiceProvider;

final class MediaUploadRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlMediaUploadStore::class);
        $this->app->alias(PostgreSqlMediaUploadStore::class, MediaUploadStore::class);
    }
}
