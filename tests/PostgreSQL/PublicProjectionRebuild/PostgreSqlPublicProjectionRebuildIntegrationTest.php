<?php

namespace Tests\PostgreSQL\PublicProjectionRebuild;

use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifestEntry;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationTransition;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionMapper;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionReader;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionWriter;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationManager;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationValidator;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicListingReadModelFixture;

final class PostgreSqlPublicProjectionRebuildIntegrationTest extends TestCase
{
    private const string ACTIVE = '98000000-0000-4000-8000-000000000001';

    private const string CANDIDATE = '98000000-0000-4000-8000-000000000002';

    private const string LISTING = '98000000-0000-4000-8000-000000000011';

    private PDO $connection;

    private PostgreSqlPublicProjectionGenerationManager $manager;

    private PostgreSqlPublicListingProjectionMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->connection->exec("INSERT INTO public_projection.generations (generation_id,state) VALUES ('".self::ACTIVE."','active')");
        $this->mapper = new PostgreSqlPublicListingProjectionMapper;
        $validator = new PostgreSqlPublicProjectionGenerationValidator($this->connection, $this->mapper);
        $this->manager = new PostgreSqlPublicProjectionGenerationManager($this->connection, $validator);
    }

    public function test_candidate_creation_is_idempotent(): void
    {
        $candidate = $this->candidate();

        self::assertSame(PublicProjectionGenerationTransition::Applied, $this->manager->createCandidate($candidate));
        self::assertSame(PublicProjectionGenerationTransition::AlreadyApplied, $this->manager->createCandidate($candidate));
    }

    public function test_candidate_is_invisible_until_atomic_activation_then_rollback_restores_previous_generation(): void
    {
        $this->write($this->active(), 'annonces/ancienne');
        $this->manager->createCandidate($this->candidate());
        $this->write($this->candidate(), 'annonces/nouvelle');
        $reader = new PostgreSqlPublicListingProjectionReader($this->connection, $this->mapper);

        self::assertNull($reader->findByCanonicalPath('annonces/nouvelle'));
        self::assertNotNull($reader->findByCanonicalPath('annonces/ancienne'));
        self::assertSame(PublicProjectionGenerationTransition::Applied, $this->manager->activate($this->candidate(), $this->manifest()));
        self::assertNull($reader->findByCanonicalPath('annonces/ancienne'));
        self::assertNotNull($reader->findByCanonicalPath('annonces/nouvelle'));
        self::assertSame(PublicProjectionGenerationTransition::Applied, $this->manager->rollback($this->active()));
        self::assertNotNull($reader->findByCanonicalPath('annonces/ancienne'));
        self::assertNull($reader->findByCanonicalPath('annonces/nouvelle'));
    }

    public function test_activation_participates_in_external_transaction_and_rolls_back_completely(): void
    {
        $this->manager->createCandidate($this->candidate());
        $this->connection->beginTransaction();
        $this->write($this->candidate(), 'annonces/candidate');
        self::assertSame(PublicProjectionGenerationTransition::Applied, $this->manager->activate($this->candidate(), $this->manifest()));
        $this->connection->rollBack();

        self::assertSame('active', $this->state(self::ACTIVE));
        self::assertSame('candidate', $this->state(self::CANDIDATE));
    }

    public function test_validation_proves_checksum_watermark_progress_lag_and_divergence(): void
    {
        $this->manager->createCandidate($this->candidate());
        $watermark = $this->watermark();
        $manifest = new PublicProjectionGenerationManifest([new PublicProjectionGenerationManifestEntry(self::LISTING, $watermark)]);
        $validator = new PostgreSqlPublicProjectionGenerationValidator($this->connection, $this->mapper);

        $empty = $validator->validate($this->candidate(), $manifest);
        self::assertSame([0, 1, 0], [$empty->progressPercent(), $empty->lag(), $empty->divergenceCount()]);
        self::assertSame(PublicProjectionGenerationTransition::ValidationFailed, $this->manager->activate($this->candidate(), $manifest));
        self::assertSame('active', $this->state(self::ACTIVE));

        $this->write($this->candidate(), 'annonces/candidate');
        $valid = $validator->validate($this->candidate(), $manifest);
        self::assertTrue($valid->isValid());
        self::assertSame([100, 0, 0], [$valid->progressPercent(), $valid->lag(), $valid->divergenceCount()]);

        $this->connection->exec("UPDATE public_projection.listing_projections SET payload_checksum='".str_repeat('0', 64)."' WHERE generation_id='".self::CANDIDATE."'");
        $corrupt = $validator->validate($this->candidate(), $manifest);
        self::assertFalse($corrupt->isValid());
        self::assertSame([self::LISTING], $corrupt->corruptListingIds);
    }

    public function test_invalid_transitions_are_typed_and_do_not_mutate_state(): void
    {
        self::assertSame(PublicProjectionGenerationTransition::GenerationNotFound, $this->manager->activate($this->candidate(), $this->manifest()));
        self::assertSame(PublicProjectionGenerationTransition::AlreadyApplied, $this->manager->rollback($this->active()));
        self::assertSame('active', $this->state(self::ACTIVE));
    }

    public function test_concurrent_atomic_switches_leave_exactly_one_active_generation_without_projection_loss(): void
    {
        $second = PublicProjectionGenerationId::fromString('98000000-0000-4000-8000-000000000003');
        $this->manager->createCandidate($this->candidate());
        $this->manager->createCandidate($second);
        $this->write($this->candidate(), 'annonces/candidate-one');
        $this->write($second, 'annonces/candidate-two');

        $results = $this->runActivationWorkers([self::CANDIDATE, $second->value]);

        self::assertSame(['applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM public_projection.generations WHERE state='active'")->fetchColumn());
        self::assertSame(2, (int) $this->connection->query("SELECT count(*) FROM public_projection.listing_projections WHERE generation_id IN ('".self::CANDIDATE."','".$second->value."')")->fetchColumn());
    }

    private function write(PublicProjectionGenerationId $generation, string $canonical): void
    {
        $reader = new PostgreSqlPublicListingProjectionReader($this->connection, $this->mapper);
        $writer = new PostgreSqlPublicListingProjectionWriter($this->connection, $this->mapper, $reader);
        $model = PublicListingReadModelFixture::make(listingId: self::LISTING, canonicalUrl: 'https://appart.sn/'.$canonical);
        $result = $generation->equals($this->active())
            ? $writer->applyCurrent(PublicListingProjectionRecord::current(self::LISTING, $canonical, $model, $this->watermark(), $generation))
            : $writer->writeCandidate(PublicListingProjectionRecord::current(self::LISTING, $canonical, $model, $this->watermark(), $generation));
        self::assertSame(PublicProjectionWriteResult::Applied, $result);
    }

    private function watermark(): PublicProjectionWatermark
    {
        return new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, 1);
    }

    private function manifest(): PublicProjectionGenerationManifest
    {
        return new PublicProjectionGenerationManifest([new PublicProjectionGenerationManifestEntry(self::LISTING, $this->watermark())]);
    }

    private function active(): PublicProjectionGenerationId
    {
        return PublicProjectionGenerationId::fromString(self::ACTIVE);
    }

    private function candidate(): PublicProjectionGenerationId
    {
        return PublicProjectionGenerationId::fromString(self::CANDIDATE);
    }

    private function state(string $generation): string
    {
        $statement = $this->connection->prepare('SELECT state FROM public_projection.generations WHERE generation_id=:generation');
        $statement->execute(['generation' => $generation]);

        return (string) $statement->fetchColumn();
    }

    /** @param list<string> $generations @return list<string> */
    private function runActivationWorkers(array $generations): array
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-generation-switch-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ($generations as $index => $generation) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'generation-activation-worker.php', $barrier, (string) ($index + 1), $generation], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start generation activation worker.');
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
                throw new RuntimeException('Generation activation worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        return $results;
    }
}
