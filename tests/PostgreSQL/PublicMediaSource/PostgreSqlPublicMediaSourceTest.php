<?php

namespace Tests\PostgreSQL\PublicMediaSource;

use App\Application\PublicMediaSource\PublicMediaReadStatus;
use App\Application\PublicMediaSource\PublicMediaWriteResult;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaMapper;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaReader;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaWriter;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicMediaDecisionFixture;

final class PostgreSqlPublicMediaSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlPublicMediaMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->mapper = new PostgreSqlPublicMediaMapper;
    }

    public function test_missing_write_read_idempotence_and_revision_contract(): void
    {
        $decision = PublicMediaDecisionFixture::make();
        self::assertSame(PublicMediaReadStatus::Missing, $this->reader()->read($decision->mediaCollectionId)->status);
        self::assertSame(PublicMediaWriteResult::Applied, $this->writer()->store($decision));
        self::assertSame(PublicMediaWriteResult::AlreadyApplied, $this->writer()->store($decision));

        $result = $this->reader()->read($decision->mediaCollectionId);
        self::assertSame(PublicMediaReadStatus::Found, $result->status);
        self::assertEquals($decision, $result->decision);
        self::assertEquals($decision->revision, $this->reader()->stableRevisionForMediaCollection($decision->mediaCollectionId));
    }

    public function test_corrupt_payload_or_checksum_is_explicit(): void
    {
        $decision = PublicMediaDecisionFixture::make();
        $this->writer()->store($decision);
        $zeros = str_repeat('0', 64);
        $this->connection->exec("UPDATE public_media.decisions SET revision_checksum='{$zeros}',payload_checksum='{$zeros}'");
        self::assertSame(PublicMediaReadStatus::Corrupted, $this->reader()->read($decision->mediaCollectionId)->status);

        PostgreSqlTestEnvironment::reset($this->connection);
        $this->writer()->store($decision);
        $payload = '{"cover":null,"gallery":[]}';
        $statement = $this->connection->prepare('UPDATE public_media.decisions SET payload=CAST(:payload AS jsonb)');
        $statement->execute(['payload' => $payload]);
        self::assertSame(PublicMediaReadStatus::Corrupted, $this->reader()->read($decision->mediaCollectionId)->status);
    }

    public function test_obsolete_and_same_version_divergence_are_explicit(): void
    {
        self::assertSame(PublicMediaWriteResult::Applied, $this->writer()->store(PublicMediaDecisionFixture::make(2)));
        self::assertSame(PublicMediaWriteResult::RejectedObsolete, $this->writer()->store(PublicMediaDecisionFixture::make(1)));
        self::assertSame(PublicMediaWriteResult::Divergent, $this->writer()->store(PublicMediaDecisionFixture::make(2, 'media:other-cover')));
    }

    public function test_atomic_constraint_and_external_rollback_are_complete(): void
    {
        $decision = PublicMediaDecisionFixture::make();
        $this->connection->beginTransaction();
        self::assertSame(PublicMediaWriteResult::Applied, $this->writer()->store($decision));
        $this->connection->rollBack();
        self::assertSame(PublicMediaReadStatus::Missing, $this->reader()->read($decision->mediaCollectionId)->status);

        $this->expectException(PDOException::class);
        $parameters = $this->mapper->parameters($decision);
        $parameters['revision_checksum'] = str_repeat('0', 64);
        $statement = $this->connection->prepare('INSERT INTO public_media.decisions(media_collection_id,version,causation_key,revision_checksum,payload,payload_checksum) VALUES(:media_collection_id,:version,:causation,:revision_checksum,CAST(:payload AS jsonb),:payload_checksum)');
        $statement->execute($parameters);
    }

    public function test_concurrent_identical_writes_converge_without_double_effect(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-public-media-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Public Media worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            if (proc_close($process) !== 0 || $error !== '') {
                throw new RuntimeException('Public Media worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM public_media.decisions')->fetchColumn());
    }

    private function reader(): PostgreSqlPublicMediaReader
    {
        return new PostgreSqlPublicMediaReader($this->connection, $this->mapper);
    }

    private function writer(): PostgreSqlPublicMediaWriter
    {
        return new PostgreSqlPublicMediaWriter($this->connection, $this->mapper);
    }
}
