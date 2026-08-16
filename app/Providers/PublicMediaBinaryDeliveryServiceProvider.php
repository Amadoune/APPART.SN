<?php

namespace App\Providers;

use App\Application\PublicMediaBinaryDelivery\Contract\PublicMediaBinaryContentReader;
use App\Application\PublicMediaBinaryDelivery\Contract\PublicMediaBinaryOwnerSourceReader;
use App\Application\PublicMediaBinaryDelivery\Contract\ResolvePublicMediaBinaryV1;
use App\Application\PublicMediaBinaryDelivery\DeterministicPublicMediaBinaryResolverV1;
use App\Infrastructure\PublicMediaBinaryDelivery\LaravelFilesystemPublicMediaBinaryContentReader;
use App\Infrastructure\PublicMediaBinaryDelivery\PostgreSqlPublicMediaBinaryOwnerSourceReader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\ServiceProvider;

final class PublicMediaBinaryDeliveryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlPublicMediaBinaryOwnerSourceReader::class, static fn (Application $app): PostgreSqlPublicMediaBinaryOwnerSourceReader => new PostgreSqlPublicMediaBinaryOwnerSourceReader(
            $app->make(DatabaseManager::class)->connection('pgsql')->getPdo(),
        ));
        $this->app->alias(PostgreSqlPublicMediaBinaryOwnerSourceReader::class, PublicMediaBinaryOwnerSourceReader::class);
        $this->app->singleton(LaravelFilesystemPublicMediaBinaryContentReader::class, static fn (Application $app): LaravelFilesystemPublicMediaBinaryContentReader => new LaravelFilesystemPublicMediaBinaryContentReader(
            $app->make(FilesystemManager::class)->disk('media'),
        ));
        $this->app->alias(LaravelFilesystemPublicMediaBinaryContentReader::class, PublicMediaBinaryContentReader::class);
        $this->app->singleton(DeterministicPublicMediaBinaryResolverV1::class);
        $this->app->alias(DeterministicPublicMediaBinaryResolverV1::class, ResolvePublicMediaBinaryV1::class);
    }
}
