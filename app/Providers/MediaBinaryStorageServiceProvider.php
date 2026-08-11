<?php

namespace App\Providers;

use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryObjectStore;
use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryStorageAuthorityV1;
use Appart\Modules\Media\Application\BinaryStorage\DeterministicMediaBinaryStorageAuthority;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Infrastructure\BinaryStorage\LaravelFilesystemMediaBinaryObjectStore;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\ServiceProvider;

final class MediaBinaryStorageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            LaravelFilesystemMediaBinaryObjectStore::class,
            static fn (Application $app): LaravelFilesystemMediaBinaryObjectStore => new LaravelFilesystemMediaBinaryObjectStore(
                $app->make(FilesystemManager::class)->disk('media'),
            ),
        );
        $this->app->alias(LaravelFilesystemMediaBinaryObjectStore::class, MediaBinaryObjectStore::class);
        $this->app->singleton(
            DeterministicMediaBinaryStorageAuthority::class,
            static fn (Application $app): DeterministicMediaBinaryStorageAuthority => new DeterministicMediaBinaryStorageAuthority(
                $app->make(MediaBinaryObjectStore::class),
                $app->make(MediaAssetStore::class),
            ),
        );
        $this->app->alias(DeterministicMediaBinaryStorageAuthority::class, MediaBinaryStorageAuthorityV1::class);
    }
}
