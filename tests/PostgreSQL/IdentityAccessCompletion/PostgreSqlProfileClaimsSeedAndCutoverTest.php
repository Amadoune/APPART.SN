<?php

namespace Tests\PostgreSQL\IdentityAccessCompletion;

use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\ProfileClaimsCutoverStatus;
use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\ProfileClaimsSeedNormalizer;
use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\ProfileClaimsSeedSourceState;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\HistoricalAccountPersistenceSnapshotV1;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\HistoricalAccountProfileClaimsSeedMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlProfileClaimsSeedAndCutover;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlProfileClaimsSeedAndCutoverTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function seed_is_atomic_idempotent_and_transfers_authority_explicitly(): void
    {
        $runId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $sources = [
            $this->seedSource('10000000-0000-4000-8000-000000000001', 'one@example.test', '+221770000001', 'Person One'),
            $this->seedSource('10000000-0000-4000-8000-000000000002', 'two@example.test', '+221770000002', 'Person Two'),
        ];

        $result = $this->cutover()->seedAndCutOver($runId, $sources, new DeterministicTestSeedProtector, $this->at());
        self::assertSame(ProfileClaimsCutoverStatus::Committed, $result->status);
        self::assertSame(2, $result->profiles);
        self::assertSame(4, $result->claims);
        self::assertSame(2, $this->tableCount('user_profiles'));
        self::assertSame(4, $this->tableCount('identity_claims'));
        self::assertSame('Profile', $this->authority());

        $replay = $this->cutover()->seedAndCutOver($runId, array_reverse($sources), new DeterministicTestSeedProtector, $this->at());
        self::assertSame(ProfileClaimsCutoverStatus::IdempotentReplay, $replay->status);
        self::assertSame(2, $this->tableCount('user_profiles'));
        self::assertSame(4, $this->tableCount('identity_claims'));
    }

    #[Test]
    public function duplicate_historical_claims_are_quarantined_without_partial_seed(): void
    {
        $result = $this->cutover()->seedAndCutOver(
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            [
                $this->seedSource('20000000-0000-4000-8000-000000000001', 'same@example.test', '+221770000011', 'Person One'),
                $this->seedSource('20000000-0000-4000-8000-000000000002', 'same@example.test', '+221770000012', 'Person Two'),
            ],
            new DeterministicTestSeedProtector,
            $this->at(),
        );

        self::assertSame(ProfileClaimsCutoverStatus::Quarantined, $result->status);
        self::assertContains('DuplicateHistoricalClaim', $result->divergences);
        self::assertSame(0, $this->tableCount('user_profiles'));
        self::assertSame(0, $this->tableCount('identity_claims'));
        self::assertSame('Historical', $this->authority());
        self::assertGreaterThan(0, $this->tableCount('profile_claim_seed_quarantine'));
    }

    #[Test]
    public function rollback_restores_historical_authority_and_removes_only_unchanged_seed_rows(): void
    {
        $runId = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
        $source = $this->seedSource('30000000-0000-4000-8000-000000000001', 'rollback@example.test', '+221770000021', 'Rollback Person');
        self::assertSame(
            ProfileClaimsCutoverStatus::Committed,
            $this->cutover()->seedAndCutOver($runId, [$source], new DeterministicTestSeedProtector, $this->at())->status,
        );

        $result = $this->cutover()->rollback(
            $runId,
            'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
            new DateTimeImmutable('2026-01-01T00:01:00Z'),
        );
        self::assertSame(ProfileClaimsCutoverStatus::RolledBack, $result->status);
        self::assertSame('Historical', $this->authority());
        self::assertSame(0, $this->tableCount('user_profiles'));
        self::assertSame(0, $this->tableCount('identity_claims'));
        self::assertSame('RolledBack', $this->connection->query(
            "SELECT state FROM identity_access_completion.profile_claim_seed_runs WHERE run_id='{$runId}'",
        )->fetchColumn());
    }

    private function cutover(): PostgreSqlProfileClaimsSeedAndCutover
    {
        return new PostgreSqlProfileClaimsSeedAndCutover(
            $this->connection,
            new ProfileClaimsSeedNormalizer,
            new IdentityAccessCompletionPersistenceMapper,
        );
    }

    private function snapshot(string $id, string $email, string $phone, string $name): HistoricalAccountPersistenceSnapshotV1
    {
        $registered = new DateTimeImmutable('2025-01-01T00:00:00Z');
        $account = Account::register(
            AccountId::fromString($id),
            EmailAddress::fromString($email),
            PhoneNumber::fromString($phone),
            PersonName::fromString($name),
            PasswordHash::fromString('$argon2id$v=19$m=65536,t=4,p=1$c2FsdA$ZGlnaWVzdA'),
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 32)),
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 32)),
            new DateTimeImmutable('2025-01-01T01:00:00Z'),
            $registered,
        );

        return (new AccountPersistenceMapper)->snapshot($account);
    }

    private function seedSource(string $id, string $email, string $phone, string $name): ProfileClaimsSeedSourceState
    {
        return (new HistoricalAccountProfileClaimsSeedMapper)->map(
            $this->snapshot($id, $email, $phone, $name),
        );
    }

    private function at(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-01-01T00:00:00Z');
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query(
            "SELECT count(*) FROM identity_access_completion.{$table}",
        )->fetchColumn();
    }

    private function authority(): string
    {
        return (string) $this->connection->query(
            "SELECT authority FROM identity_access_completion.profile_claim_authority WHERE authority_key='ProfileClaims'",
        )->fetchColumn();
    }
}
