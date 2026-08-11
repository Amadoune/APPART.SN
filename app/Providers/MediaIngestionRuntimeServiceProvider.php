<?php

namespace App\Providers;

use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeAvailabilityPolicy;
use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeV1;
use App\Application\MediaIngestionRuntime\DeterministicMediaIngestionRuntimeAvailabilityPolicy;
use App\Application\MediaIngestionRuntime\DeterministicMediaIngestionRuntimeV1;
use Appart\Modules\Media\Application\Attachment\Contract\AttachReadyMediaAssetV1;
use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryStorageAuthorityV1;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaProcessingStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaQuotaStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaUploadStore;
use Appart\Modules\Media\Application\ReadyAsset\Contract\MediaAssetReadinessV1;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class MediaIngestionRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            MediaIngestionRuntimeAvailabilityPolicy::class,
            static fn (Application $app): MediaIngestionRuntimeAvailabilityPolicy => new DeterministicMediaIngestionRuntimeAvailabilityPolicy([
                'media_ready_asset_attachment' => self::compatible($app, AttachReadyMediaAssetV1::class),
                'media_binary_storage' => self::compatible($app, MediaBinaryStorageAuthorityV1::class),
                'media_asset_readiness' => self::compatible($app, MediaAssetReadinessV1::class),
                'media_upload' => self::compatible($app, MediaUploadStore::class),
                'media_asset' => self::compatible($app, MediaAssetStore::class),
                'media_processing' => self::compatible($app, MediaProcessingStore::class),
                'media_quota' => self::compatible($app, MediaQuotaStore::class),
            ]),
        );
        $this->app->singleton(DeterministicMediaIngestionRuntimeV1::class);
        $this->app->alias(DeterministicMediaIngestionRuntimeV1::class, MediaIngestionRuntimeV1::class);
    }

    private static function compatible(Application $app, string $contract): bool
    {
        return $app->bound($contract) && $app->make($contract) instanceof $contract;
    }
}
