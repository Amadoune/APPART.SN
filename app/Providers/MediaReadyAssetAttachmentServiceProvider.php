<?php

namespace App\Providers;

use App\Infrastructure\MediaAttachment\CompositeMediaPropertyCatalog;
use App\Infrastructure\MediaAttachment\PropertyAuthoringMediaCatalogAdapter;
use App\Infrastructure\MediaAttachment\RegistryMediaPropertyCatalog;
use Appart\Modules\Media\Application\Attachment\Contract\AttachReadyMediaAssetV1;
use Appart\Modules\Media\Application\Attachment\Contract\MediaAttachmentIntentStore;
use Appart\Modules\Media\Application\Attachment\Contract\MediaAttachmentTransaction;
use Appart\Modules\Media\Application\Attachment\DeterministicAttachReadyMediaAsset;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAttachmentIntentStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAttachmentTransaction;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class MediaReadyAssetAttachmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlMediaAttachmentTransaction::class);
        $this->app->alias(PostgreSqlMediaAttachmentTransaction::class, MediaAttachmentTransaction::class);
        $this->app->singleton(PostgreSqlMediaAttachmentIntentStore::class);
        $this->app->alias(PostgreSqlMediaAttachmentIntentStore::class, MediaAttachmentIntentStore::class);
        $this->app->singleton(RegistryMediaPropertyCatalog::class);
        $this->app->singleton(PropertyAuthoringMediaCatalogAdapter::class);
        $this->app->singleton(CompositeMediaPropertyCatalog::class);
        $this->app->alias(CompositeMediaPropertyCatalog::class, PropertyCatalog::class);
        $this->app->singleton(
            DeterministicAttachReadyMediaAsset::class,
            static fn (Application $app): DeterministicAttachReadyMediaAsset => new DeterministicAttachReadyMediaAsset(
                $app->make(MediaAssetStore::class),
                $app->make(MediaCollectionRegistry::class),
                $app->make(PropertyCatalog::class),
                $app->make(MediaAttachmentIntentStore::class),
                $app->make(MediaAttachmentTransaction::class),
            ),
        );
        $this->app->alias(DeterministicAttachReadyMediaAsset::class, AttachReadyMediaAssetV1::class);
    }
}
