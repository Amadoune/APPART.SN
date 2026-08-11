<?php

namespace Tests\PostgreSQL\MediaReadyAssetAttachment;

use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetCommandV1;
use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetResultV1;
use Appart\Modules\Media\Application\Attachment\DeterministicAttachReadyMediaAsset;
use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaIngestionStateMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAssetStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAttachmentIntentStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAttachmentTransaction;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionRepository;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaReadyAssetAttachmentTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function ready_asset_is_atomically_attached_and_replay_creates_no_duplicate(): void
    {
        $assets = new PostgreSqlMediaAssetStore($this->connection, new MediaIngestionStateMapper);
        $mediaId = $this->id(3);
        $checksum = hash('sha256', 'postgresql-ready-asset');
        $payload = ['ownerId' => $this->id(9), 'contentChecksum' => $checksum, 'storageKey' => 'owners/'.$this->id(9).'/assets/'.$mediaId, 'bytes' => 22];
        self::assertSame(MediaIngestionPersistenceWriteResult::Applied, $assets->save(new MediaAssetState(
            $mediaId,
            'quarantined',
            1,
            $this->id(4),
            hash('sha256', 'quarantined-intent'),
            $payload,
        ), 0));
        self::assertSame(MediaIngestionPersistenceWriteResult::Applied, $assets->save(new MediaAssetState(
            $mediaId,
            'ready',
            2,
            $this->id(6),
            hash('sha256', 'ready-intent'),
            $payload,
        ), 1));
        $collections = new PostgreSqlMediaCollectionRepository($this->connection, new MediaCollectionMapper);
        $service = new DeterministicAttachReadyMediaAsset(
            $assets,
            $collections,
            new PostgreSqlAttachmentPropertyCatalog,
            new PostgreSqlMediaAttachmentIntentStore($this->connection),
            new PostgreSqlMediaAttachmentTransaction($this->connection),
        );
        $command = new AttachReadyMediaAssetCommandV1(
            $this->id(5), hash('sha256', 'attach'), $this->id(2), $this->id(1), $mediaId, $checksum, 1, null, 'owner', 0, '2026-08-10T11:00:00+00:00',
        );

        self::assertSame(AttachReadyMediaAssetResultV1::Applied, $service->attach($command));
        self::assertSame(AttachReadyMediaAssetResultV1::AlreadyApplied, $service->attach($command));
        $collection = $collections->find(MediaCollectionId::fromString($command->collectionId));
        self::assertNotNull($collection);
        self::assertSame($command->propertyId, $collection->propertyId()->value);
        self::assertCount(1, $collection->items());
        self::assertSame($mediaId, $collection->items()[0]->id->value);
        self::assertSame(1, $collection->version());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM media.media_attachment_intents')->fetchColumn());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM media.media_collections')->fetchColumn());
        self::assertFalse($this->connection->inTransaction());
    }

    private function id(int $suffix): string
    {
        return sprintf('67000000-0000-4000-8000-%012d', $suffix);
    }
}

final readonly class PostgreSqlAttachmentPropertyCatalog implements PropertyCatalog
{
    public function exists(PropertyId $propertyId): bool
    {
        return true;
    }
}
