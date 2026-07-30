<?php

namespace App\Providers;

use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaQuotaStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaQuotaStore;
use Illuminate\Support\ServiceProvider;

final class MediaQuotaRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlMediaQuotaStore::class);
        $this->app->alias(PostgreSqlMediaQuotaStore::class, MediaQuotaStore::class);
    }
}
