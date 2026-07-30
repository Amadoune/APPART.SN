<?php

namespace Tests\PostgreSQL\MediaIngestionPersistence;

use Appart\Modules\Media\Application\Attachment\MediaAttachmentIntent;
use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\IngestionPersistence\MediaProcessingState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaQuotaState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaUploadState;
use Appart\Modules\Media\Infrastructure\Persistence\MediaIngestionStateMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAssetStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAttachmentIntentStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaProcessingStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaQuotaStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaUploadStore;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaIngestionPersistenceTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function four_owner_stores_round_trip_and_converge_by_intent(): void
    {
        $mapper = new MediaIngestionStateMapper;
        $cases = [
            [new PostgreSqlMediaUploadStore($this->connection, $mapper), new MediaUploadState($this->id(1), 'reserved', 1, $this->intent(1), $this->checksum('upload'), ['bytes' => 100])],
            [new PostgreSqlMediaAssetStore($this->connection, $mapper), new MediaAssetState($this->id(2), 'quarantined', 1, $this->intent(2), $this->checksum('asset'), ['object' => 'opaque'])],
            [new PostgreSqlMediaProcessingStore($this->connection, $mapper), new MediaProcessingState($this->id(3), 'pending', 1, $this->intent(3), $this->checksum('processing'), ['recipe' => 'v1'])],
            [new PostgreSqlMediaQuotaStore($this->connection, $mapper), new MediaQuotaState($this->id(4), 'available', 1, $this->intent(4), $this->checksum('quota'), ['remaining' => 1000])],
        ];

        foreach ($cases as [$store, $state]) {
            self::assertSame(MediaIngestionPersistenceWriteResult::Applied, $store->save($state, 0));
            self::assertSame(MediaIngestionPersistenceWriteResult::AlreadyApplied, $store->save($state, 0));
            self::assertSame($state->payload, $store->read($state->id)?->payload);
        }
    }

    #[Test]
    public function intent_history_detects_divergence_after_later_versions(): void
    {
        $store = new PostgreSqlMediaUploadStore($this->connection, new MediaIngestionStateMapper);
        $first = new MediaUploadState($this->id(1), 'reserved', 1, $this->intent(1), $this->checksum('first'), []);
        $second = new MediaUploadState($this->id(1), 'receiving', 2, $this->intent(2), $this->checksum('second'), []);
        self::assertSame(MediaIngestionPersistenceWriteResult::Applied, $store->save($first, 0));
        self::assertSame(MediaIngestionPersistenceWriteResult::Applied, $store->save($second, 1));
        self::assertSame(MediaIngestionPersistenceWriteResult::AlreadyApplied, $store->save($first, 0));
        $divergent = new MediaUploadState($this->id(1), 'reserved', 1, $this->intent(1), $this->checksum('different'), []);
        self::assertSame(MediaIngestionPersistenceWriteResult::DivergentIntent, $store->save($divergent, 0));
        self::assertSame(2, $store->read($this->id(1))?->version);
    }

    #[Test]
    public function optimistic_lock_rejects_stale_writes_without_partial_intent(): void
    {
        $store = new PostgreSqlMediaAssetStore($this->connection, new MediaIngestionStateMapper);
        $state = new MediaAssetState($this->id(2), 'quarantined', 1, $this->intent(1), $this->checksum('one'), []);
        self::assertSame(MediaIngestionPersistenceWriteResult::Applied, $store->save($state, 0));
        $stale = new MediaAssetState($this->id(2), 'inspecting', 2, $this->intent(2), $this->checksum('two'), []);
        self::assertSame(MediaIngestionPersistenceWriteResult::VersionConflict, $store->save($stale, 0));
        self::assertSame(0, (int) $this->connection->query("SELECT count(*) FROM media_ingestion.asset_intents WHERE intent_id='{$this->intent(2)}'")->fetchColumn());
    }

    #[Test]
    public function attachment_intent_is_owner_scoped_and_reconstructible(): void
    {
        $store = new PostgreSqlMediaAttachmentIntentStore($this->connection);
        $intent = new MediaAttachmentIntent($this->intent(9), $this->checksum('attachment'), $this->id(10), $this->id(11), $this->id(12));
        self::assertTrue($store->reserve($intent));
        self::assertFalse($store->reserve($intent));
        self::assertNull($store->find($intent->intentId)?->aggregateVersion);
        $store->markApplied($intent->intentId, 1);
        self::assertSame(1, $store->find($intent->intentId)?->aggregateVersion);
    }

    private function id(int $suffix): string
    {
        return sprintf('58000000-0000-4000-8000-%012d', $suffix);
    }

    private function intent(int $suffix): string
    {
        return sprintf('58000000-0000-4001-8000-%012d', $suffix);
    }

    private function checksum(string $value): string
    {
        return hash('sha256', $value);
    }
}
