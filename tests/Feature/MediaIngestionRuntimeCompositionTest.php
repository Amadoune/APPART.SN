<?php

namespace Tests\Feature;

use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeV1;
use App\Application\MediaIngestionRuntime\MediaIngestionRuntimeStatus;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Infrastructure\MediaAttachment\CompositeMediaPropertyCatalog;
use App\Infrastructure\MediaAttachment\PropertyAuthoringMediaCatalogAdapter;
use Appart\Modules\Media\Application\Attachment\Contract\AttachReadyMediaAssetV1;
use Appart\Modules\Media\Application\Attachment\DeterministicAttachReadyMediaAsset;
use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryStorageAuthorityV1;
use Appart\Modules\Media\Application\BinaryStorage\DeterministicMediaBinaryStorageAuthority;
use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaProcessingStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaQuotaStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaUploadStore;
use Appart\Modules\Media\Application\ReadyAsset\Contract\MediaAssetReadinessV1;
use Appart\Modules\Media\Application\ReadyAsset\DeterministicMediaAssetReadiness;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAssetStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaProcessingStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaQuotaStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaUploadStore;
use PDO;
use ReflectionProperty;
use Tests\TestCase;

final class MediaIngestionRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(PDO::class, static fn (): PDO => new PDO('sqlite::memory:'));
    }

    public function test_owner_bindings_are_lazy_singletons_sharing_one_connection(): void
    {
        foreach ([MediaUploadStore::class, MediaAssetStore::class, MediaProcessingStore::class, MediaQuotaStore::class] as $contract) {
            self::assertFalse($this->app->resolved($contract));
        }

        $runtime = $this->app->make(MediaIngestionRuntimeV1::class);
        self::assertInstanceOf(DeterministicAttachReadyMediaAsset::class, $runtime->attachment());
        self::assertSame($runtime->attachment(), $this->app->make(AttachReadyMediaAssetV1::class));
        self::assertInstanceOf(CompositeMediaPropertyCatalog::class, $this->app->make(PropertyCatalog::class));
        self::assertSame(
            $this->app->make(PropertyAuthoringMediaCatalogAdapter::class),
            (new ReflectionProperty($this->app->make(PropertyCatalog::class), 'authoring'))->getValue($this->app->make(PropertyCatalog::class)),
        );
        self::assertInstanceOf(DeterministicMediaBinaryStorageAuthority::class, $runtime->binary());
        self::assertSame($runtime->binary(), $this->app->make(MediaBinaryStorageAuthorityV1::class));
        self::assertInstanceOf(DeterministicMediaAssetReadiness::class, $runtime->readiness());
        self::assertSame($runtime->readiness(), $this->app->make(MediaAssetReadinessV1::class));
        $stores = [
            [$runtime->upload(), PostgreSqlMediaUploadStore::class],
            [$runtime->asset(), PostgreSqlMediaAssetStore::class],
            [$runtime->processing(), PostgreSqlMediaProcessingStore::class],
            [$runtime->quota(), PostgreSqlMediaQuotaStore::class],
        ];
        foreach ($stores as [$store, $class]) {
            self::assertInstanceOf($class, $store);
            self::assertSame($this->app->make(PDO::class), (new ReflectionProperty($store, 'connection'))->getValue($store));
        }
    }

    public function test_runtime_is_fail_closed_and_does_not_extend_runtime_health(): void
    {
        $runtime = $this->app->make(MediaIngestionRuntimeV1::class);

        self::assertSame(MediaIngestionRuntimeStatus::Ready, $runtime->inspect()->status);
        self::assertNull($runtime->inspect()->code);
        self::assertSame($runtime, $this->app->make(MediaIngestionRuntimeV1::class));
        self::assertCount(60, PublicProjectionRuntimeRequirements::certified());
        self::assertFalse($this->app->make(PDO::class)->inTransaction());
    }
}
