<?php

namespace App\Providers;

use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryObjectStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\ReadyAsset\Contract\MediaAssetReadinessV1;
use Appart\Modules\Media\Application\ReadyAsset\DeterministicMediaAssetReadiness;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class MediaAssetReadinessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            DeterministicMediaAssetReadiness::class,
            static fn (Application $app): DeterministicMediaAssetReadiness => new DeterministicMediaAssetReadiness(
                $app->make(MediaBinaryObjectStore::class),
                $app->make(MediaAssetStore::class),
            ),
        );
        $this->app->alias(DeterministicMediaAssetReadiness::class, MediaAssetReadinessV1::class);
    }
}
