<?php

namespace Tests\PostgreSQL\MediaIngestionRuntime;

use App\Application\MediaIngestionRuntime\DeterministicMediaIngestionRuntimeAvailabilityPolicy;
use App\Application\MediaIngestionRuntime\DeterministicMediaIngestionRuntimeV1;
use App\Application\MediaIngestionRuntime\MediaIngestionRuntimeStatus;
use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryStorageAuthorityV1;
use Appart\Modules\Media\Application\Attachment\Contract\AttachReadyMediaAssetV1;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\IngestionPersistence\MediaUploadState;
use Appart\Modules\Media\Application\ReadyAsset\Contract\MediaAssetReadinessV1;
use Appart\Modules\Media\Infrastructure\Persistence\MediaIngestionStateMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaAssetStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaProcessingStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaQuotaStore;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaUploadStore;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaIngestionRuntimeTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function runtime_composes_four_owner_stores_without_opening_a_transaction(): void
    {
        $mapper = new MediaIngestionStateMapper;
        $upload = new PostgreSqlMediaUploadStore($this->connection, $mapper);
        $runtime = new DeterministicMediaIngestionRuntimeV1(
            $this->createStub(AttachReadyMediaAssetV1::class),
            $this->createStub(MediaBinaryStorageAuthorityV1::class),
            $this->createStub(MediaAssetReadinessV1::class),
            $upload,
            new PostgreSqlMediaAssetStore($this->connection, $mapper),
            new PostgreSqlMediaProcessingStore($this->connection, $mapper),
            new PostgreSqlMediaQuotaStore($this->connection, $mapper),
            new DeterministicMediaIngestionRuntimeAvailabilityPolicy([
                'media_upload' => true,
                'media_asset' => true,
                'media_processing' => true,
                'media_quota' => true,
            ]),
        );

        self::assertSame(MediaIngestionRuntimeStatus::Ready, $runtime->inspect()->status);
        self::assertFalse($this->connection->inTransaction());
        $state = new MediaUploadState(
            '59000000-0000-4000-8000-000000000001',
            'reserved',
            1,
            '59000000-0000-4001-8000-000000000001',
            hash('sha256', 'runtime'),
            [],
        );
        self::assertSame(MediaIngestionPersistenceWriteResult::Applied, $runtime->upload()->save($state, 0));
        self::assertSame(MediaIngestionPersistenceWriteResult::AlreadyApplied, $runtime->upload()->save($state, 0));
        self::assertFalse($this->connection->inTransaction());
    }
}
