<?php

namespace Tests\PostgreSQL\ProfessionalProfileRuntime;

use App\Application\ProfessionalProfileRuntime\DeterministicProfessionalProfileRuntimeAvailabilityPolicy;
use App\Application\ProfessionalProfileRuntime\DeterministicProfessionalProfileRuntimeV1;
use App\Application\ProfessionalProfileRuntime\ProfessionalProfileRuntimeStatus;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalProfileWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicProfileState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\PublicProfileVisibility;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalPublicPortfolioStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalVerificationStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalProfilePersistenceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlProfessionalProfileRuntimeTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function runtime_composes_three_owner_stores_without_opening_a_transaction(): void
    {
        $mapper = new ProfessionalProfilePersistenceMapper;
        $profile = new PostgreSqlProfessionalPublicProfileStore($this->connection, $mapper);
        $runtime = new DeterministicProfessionalProfileRuntimeV1(
            $profile,
            new PostgreSqlProfessionalVerificationStore($this->connection, $mapper),
            new PostgreSqlProfessionalPublicPortfolioStore($this->connection, $mapper),
            new DeterministicProfessionalProfileRuntimeAvailabilityPolicy([
                'professional_public_profile' => true,
                'professional_verification' => true,
                'professional_public_portfolio' => true,
            ]),
        );

        self::assertSame(ProfessionalProfileRuntimeStatus::Ready, $runtime->inspect()->status);
        self::assertFalse($this->connection->inTransaction());
        $state = new ProfessionalPublicProfileState(
            '62000000-0000-4000-8000-000000000001',
            PublicProfileVisibility::Draft,
            'Agency',
            'Description',
            ['agency'],
            ['fr'],
            [],
            [],
            1,
            1,
            'profile-v1',
            '62000000-0000-4000-8000-000000000002',
            hash('sha256', 'runtime'),
            new DateTimeImmutable('2026-07-28T12:00:00+00:00'),
        );
        self::assertSame(ProfessionalProfileWriteResult::Applied, $runtime->publicProfile()->save($state, 0));
        self::assertSame(ProfessionalProfileWriteResult::AlreadyApplied, $runtime->publicProfile()->save($state, 0));
        self::assertFalse($this->connection->inTransaction());
    }
}
