<?php

namespace App\Providers;

use Appart\Modules\ContentSeo\Application\OwnerSource\ContentSeoOwnerSource;
use Appart\Modules\ContentSeo\Application\Runtime\ContentSeoRuntimeAvailabilityPolicy;
use Appart\Modules\ContentSeo\Application\Runtime\ContentSeoRuntimeV1;
use Appart\Modules\ContentSeo\Application\Runtime\DeterministicContentSeoRuntime;
use Appart\Modules\ContentSeo\Application\Runtime\DeterministicContentSeoRuntimeAvailabilityPolicy;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoOwnerSourceMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoOwnerSource;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

final class ContentSeoRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentSeoOwnerSourceMapper::class);
        $this->app->singleton(
            PostgreSqlContentSeoOwnerSource::class,
            static fn ($app): PostgreSqlContentSeoOwnerSource => new PostgreSqlContentSeoOwnerSource(
                $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
                $app->make(ContentSeoOwnerSourceMapper::class),
            ),
        );
        $this->app->alias(PostgreSqlContentSeoOwnerSource::class, ContentSeoOwnerSource::class);
        $this->app->singleton(
            DeterministicContentSeoRuntimeAvailabilityPolicy::class,
            static fn ($app): DeterministicContentSeoRuntimeAvailabilityPolicy => new DeterministicContentSeoRuntimeAvailabilityPolicy(
                $app->make(ContentSeoOwnerSource::class),
            ),
        );
        $this->app->alias(DeterministicContentSeoRuntimeAvailabilityPolicy::class, ContentSeoRuntimeAvailabilityPolicy::class);
        $this->app->singleton(DeterministicContentSeoRuntime::class);
        $this->app->alias(DeterministicContentSeoRuntime::class, ContentSeoRuntimeV1::class);
    }
}
