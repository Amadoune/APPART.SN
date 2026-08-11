<?php

namespace Tests\PostgreSQL\MediaAuthoringPublicSurface;

use App\Application\MediaAuthoringHttp\DeterministicMediaAuthoringHttpRuntime;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpStatus;
use App\Application\MediaIngestionRuntime\DeterministicMediaIngestionRuntimeAvailabilityPolicy;
use App\Application\MediaIngestionRuntime\DeterministicMediaIngestionRuntimeV1;
use App\Infrastructure\MediaAttachment\PropertyAuthoringMediaCatalogAdapter;
use Appart\Modules\Media\Application\Attachment\DeterministicAttachReadyMediaAsset;
use Appart\Modules\Media\Application\BinaryStorage\DeterministicMediaBinaryStorageAuthority;
use Appart\Modules\Media\Application\ReadyAsset\DeterministicMediaAssetReadiness;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaStatus;
use Appart\Modules\Media\Infrastructure\BinaryStorage\LaravelFilesystemMediaBinaryObjectStore;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaIngestionStateMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAssetStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAttachmentIntentStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAttachmentTransaction;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionRepository;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaProcessingStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaQuotaStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaUploadStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyAuthoringMapper;
use Illuminate\Support\Facades\Storage;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PostgreSqlMediaAuthoringPublicSurfaceTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function real_files_flow_to_ready_collection_and_certified_archive(): void
    {
        $owner = $this->id(1);
        $propertyId = $this->id(2);
        $properties = new PostgreSqlPropertyAuthoringStore($this->connection, new PropertyAuthoringMapper);
        $properties->save(new PropertyAuthoringState($propertyId, $owner, 1, $this->id(3), hash('sha256', 'property'), 'apartment', 'Dakar', 'Almadies'), 0);
        $stateMapper = new MediaIngestionStateMapper;
        $assets = new PostgreSqlMediaAssetStore($this->connection, $stateMapper);
        $objects = new LaravelFilesystemMediaBinaryObjectStore(Storage::disk('media'));
        $binary = new DeterministicMediaBinaryStorageAuthority($objects, $assets);
        $readiness = new DeterministicMediaAssetReadiness($objects, $assets);
        $collections = new PostgreSqlMediaCollectionRepository($this->connection, new MediaCollectionMapper);
        $attachment = new DeterministicAttachReadyMediaAsset(
            $assets,
            $collections,
            new PropertyAuthoringMediaCatalogAdapter($properties),
            new PostgreSqlMediaAttachmentIntentStore($this->connection),
            new PostgreSqlMediaAttachmentTransaction($this->connection),
        );
        $media = new DeterministicMediaIngestionRuntimeV1(
            $attachment,
            $binary,
            $readiness,
            new PostgreSqlMediaUploadStore($this->connection, $stateMapper),
            $assets,
            new PostgreSqlMediaProcessingStore($this->connection, $stateMapper),
            new PostgreSqlMediaQuotaStore($this->connection, $stateMapper),
            new DeterministicMediaIngestionRuntimeAvailabilityPolicy([
                'media_ready_asset_attachment' => true,
                'media_binary_storage' => true,
                'media_asset_readiness' => true,
                'media_upload' => true,
                'media_asset' => true,
                'media_processing' => true,
                'media_quota' => true,
            ]),
        );
        $surface = new DeterministicMediaAuthoringHttpRuntime($media, $properties, $collections);

        $first = $surface->upload($owner, $propertyId, $this->id(10), 'photo1.jpg', 'image/jpeg', $this->stream('first-real-image'), 1, 'Façade', '2026-08-10T13:00:00+00:00');
        $second = $surface->upload($owner, $propertyId, $this->id(11), 'photo2.jpg', 'image/jpeg', $this->stream('second-real-image'), 2, 'Salon', '2026-08-10T13:01:00+00:00');
        self::assertSame(MediaAuthoringHttpStatus::Created, $first->status);
        self::assertSame(MediaAuthoringHttpStatus::Created, $second->status);
        self::assertSame('ready', $assets->read((string) $first->data['mediaId'])?->state);
        self::assertSame('first-real-image', Storage::disk('media')->get('owners/'.$owner.'/assets/'.$first->data['mediaId']));

        $read = $surface->collection($owner, $propertyId);
        self::assertSame(MediaAuthoringHttpStatus::Available, $read->status);
        self::assertCount(2, $read->data['items']);
        self::assertSame(MediaAuthoringHttpStatus::NotFoundOrForbidden, $surface->collection($this->id(99), $propertyId)->status);

        $archived = $surface->archive($owner, $propertyId, (string) $first->data['mediaId'], (string) $second->data['mediaId'], '2026-08-10T13:02:00+00:00');
        self::assertSame(MediaAuthoringHttpStatus::Available, $archived->status);
        $collection = $collections->find(MediaCollectionId::fromString((string) $first->data['collectionId']));
        self::assertNotNull($collection);
        self::assertSame(MediaStatus::Archived, $collection->items()[0]->status);
        self::assertTrue($collection->items()[1]->primary);
        self::assertSame(2, (int) $this->connection->query('SELECT count(*) FROM media.media_attachment_intents')->fetchColumn());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM media.media_collections')->fetchColumn());
    }

    /** @return resource */
    private function stream(string $contents)
    {
        $stream = fopen('php://temp', 'w+b');
        self::assertIsResource($stream);
        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }

    private function id(int $suffix): string
    {
        return sprintf('71000000-0000-4000-8000-%012d', $suffix);
    }
}
