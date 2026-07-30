<?php

namespace Tests\PostgreSQL\ActiveGenerationReader;

use App\Application\ActiveGenerationReader\ActiveGenerationReadStatus;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifestEntry;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationTransition;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\Infrastructure\ActiveGenerationReader\PostgreSql\PostgreSqlActiveGenerationMapper;
use App\Infrastructure\ActiveGenerationReader\PostgreSql\PostgreSqlActiveGenerationReader;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionMapper;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionReader;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionWriter;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationManager;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationValidator;
use PDO;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicListingReadModelFixture;
use Tests\Unit\Contracts\ActiveGenerationReader\ActiveGenerationReaderContract;

final class PostgreSqlActiveGenerationReaderTest extends ActiveGenerationReaderContract
{
    private const string CANDIDATE = '99000000-0000-4000-8000-000000000002';

    private const string LISTING = '99000000-0000-4000-8000-000000000011';

    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_multiple_active_rows_are_reported_as_corrupted(): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->exec('DROP INDEX public_projection.public_projection_one_active_generation');
            $this->connection->exec("INSERT INTO public_projection.generations(generation_id,state) VALUES ('".self::GENERATION."','active'),('".self::CANDIDATE."','active')");

            self::assertSame(ActiveGenerationReadStatus::Corrupted, $this->reader()->read()->status);
        } finally {
            $this->connection->rollBack();
        }
    }

    public function test_reader_tracks_certified_activation_and_rollback_transitions(): void
    {
        $active = PublicProjectionGenerationId::fromString(self::GENERATION);
        $candidate = PublicProjectionGenerationId::fromString(self::CANDIDATE);
        $this->givenActive($active);
        $validator = new PostgreSqlPublicProjectionGenerationValidator($this->connection, new PostgreSqlPublicListingProjectionMapper);
        $manager = new PostgreSqlPublicProjectionGenerationManager($this->connection, $validator);

        self::assertSame(PublicProjectionGenerationTransition::Applied, $manager->createCandidate($candidate));
        $watermark = new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, 1);
        $record = PublicListingProjectionRecord::current(
            self::LISTING,
            'annonces/generation-candidate',
            PublicListingReadModelFixture::make(listingId: self::LISTING, canonicalUrl: 'https://appart.sn/annonces/generation-candidate'),
            $watermark,
            $candidate,
        );
        $projectionReader = new PostgreSqlPublicListingProjectionReader($this->connection, new PostgreSqlPublicListingProjectionMapper);
        $projectionWriter = new PostgreSqlPublicListingProjectionWriter($this->connection, new PostgreSqlPublicListingProjectionMapper, $projectionReader);
        self::assertSame(PublicProjectionWriteResult::Applied, $projectionWriter->writeCandidate($record));
        $manifest = new PublicProjectionGenerationManifest([new PublicProjectionGenerationManifestEntry(self::LISTING, $watermark)]);
        self::assertSame(PublicProjectionGenerationTransition::Applied, $manager->activate($candidate, $manifest));
        self::assertTrue($candidate->equals($this->reader()->read()->generation?->id ?? throw new \LogicException('Missing generation.')));
        self::assertSame(PublicProjectionGenerationTransition::Applied, $manager->rollback($active));
        self::assertTrue($active->equals($this->reader()->read()->generation?->id ?? throw new \LogicException('Missing generation.')));
    }

    public function test_concurrent_reads_are_deterministic_and_non_mutating(): void
    {
        $this->givenActive(PublicProjectionGenerationId::fromString(self::GENERATION));
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-active-generation-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-reader.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Active Generation reader.');
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
                throw new RuntimeException('Active Generation reader failed: '.$error);
            }
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame([self::GENERATION, self::GENERATION], $results);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM public_projection.generations')->fetchColumn());
    }

    protected function reader(): ActiveGenerationReader
    {
        return new PostgreSqlActiveGenerationReader($this->connection, new PostgreSqlActiveGenerationMapper);
    }

    protected function givenActive(PublicProjectionGenerationId $generationId): void
    {
        $statement = $this->connection->prepare("INSERT INTO public_projection.generations(generation_id,state) VALUES (:generation,'active')");
        $statement->execute(['generation' => $generationId->value]);
    }
}
