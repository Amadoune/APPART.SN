<?php

namespace Tests\PostgreSQL\ProfessionalProfilePersistence;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalProfileWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicPortfolioState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicProfileState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalVerificationState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\PublicProfileVisibility;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\VerificationDisposition;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalPublicPortfolioStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalVerificationStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalProfilePersistenceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlProfessionalProfilePersistenceTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_public_profile_is_idempotent_versioned_and_append_only(): void
    {
        $store = new PostgreSqlProfessionalPublicProfileStore($this->connection, new ProfessionalProfilePersistenceMapper);
        $first = $this->profile(1, 1, $this->id(11), str_repeat('a', 64));
        self::assertSame(ProfessionalProfileWriteResult::Applied, $store->save($first, 0));
        self::assertSame(ProfessionalProfileWriteResult::AlreadyApplied, $store->save($first, 0));
        self::assertSame(ProfessionalProfileWriteResult::DivergentIntent, $store->save($this->profile(1, 1, $this->id(11), str_repeat('b', 64)), 0));
        $second = $this->profile(2, 2, $this->id(12), str_repeat('c', 64));
        self::assertSame(ProfessionalProfileWriteResult::Applied, $store->save($second, 1));
        self::assertEquals($second, $store->read($this->id(1)));
        self::assertSame(2, $this->tableCount('public_profile_revisions'));
    }

    public function test_verification_keeps_decision_history_and_opaque_evidence(): void
    {
        $store = new PostgreSqlProfessionalVerificationStore($this->connection, new ProfessionalProfilePersistenceMapper);
        $pending = $this->verification(VerificationDisposition::Pending, 0, 1, $this->id(21), str_repeat('d', 64));
        self::assertSame(ProfessionalProfileWriteResult::Applied, $store->save($pending, 0));
        $verified = $this->verification(VerificationDisposition::Verified, 1, 2, $this->id(22), str_repeat('e', 64));
        self::assertSame(ProfessionalProfileWriteResult::Applied, $store->save($verified, 1));
        self::assertEquals($verified, $store->read($this->id(1)));
        self::assertSame(2, $this->tableCount('verification_decisions'));
    }

    public function test_portfolio_checkpoint_is_monotone_and_does_not_write_listing_tables(): void
    {
        $store = new PostgreSqlProfessionalPublicPortfolioStore($this->connection, new ProfessionalProfilePersistenceMapper);
        $first = $this->portfolio(10, 1, $this->id(31), str_repeat('f', 64));
        self::assertSame(ProfessionalProfileWriteResult::Applied, $store->save($first, 0));
        self::assertSame(
            ProfessionalProfileWriteResult::CheckpointRegression,
            $store->save($this->portfolio(9, 2, $this->id(32), str_repeat('1', 64)), 1),
        );
        self::assertSame(0, (int) $this->connection->query('SELECT count(*) FROM listing_lifecycle.listings')->fetchColumn());
        self::assertFalse($this->connection->inTransaction());
    }

    public function test_identical_concurrent_profile_writes_converge(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'professional-profile-'.bin2hex(random_bytes(8));
        $processes = [];

        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'profile-concurrency-worker.php', $barrier, (string) $number],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Professional Profile persistence worker.');
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
                throw new RuntimeException($error);
            }
        }

        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        sort($results);
        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, $this->tableCount('public_profiles'));
        self::assertSame(1, $this->tableCount('public_profile_revisions'));
    }

    private function profile(int $revision, int $version, string $intent, string $checksum): ProfessionalPublicProfileState
    {
        return new ProfessionalPublicProfileState($this->id(1), PublicProfileVisibility::Draft, 'Agency', 'Public description', ['agency'], ['fr'], ['phone' => '+221000000000'], [], $revision, $version, 'profile-v1', $intent, $checksum, $this->at());
    }

    private function verification(VerificationDisposition $disposition, int $sequence, int $version, string $intent, string $checksum): ProfessionalVerificationState
    {
        return new ProfessionalVerificationState($this->id(1), $disposition, ['proof:opaque'], 'verification-v1', $disposition === VerificationDisposition::Verified ? $this->id(99) : null, null, $sequence, $version, $intent, $checksum, $this->at());
    }

    private function portfolio(int $checkpoint, int $version, string $intent, string $checksum): ProfessionalPublicPortfolioState
    {
        return new ProfessionalPublicPortfolioState($this->id(1), [$this->id(50)], $checkpoint, $version, $intent, $checksum, $this->at());
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM professional_profile.{$table}")->fetchColumn();
    }

    private function id(int $suffix): string
    {
        return sprintf('61000000-0000-4000-8000-%012d', $suffix);
    }

    private function at(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-28T12:00:00+00:00');
    }
}
