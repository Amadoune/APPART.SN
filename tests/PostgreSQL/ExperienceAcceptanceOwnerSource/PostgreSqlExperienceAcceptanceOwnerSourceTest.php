<?php

namespace Tests\PostgreSQL\ExperienceAcceptanceOwnerSource;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadStatus;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceRevisionState;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceWriteResult;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\ExperienceAcceptanceOwnerSourceMapper;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\PostgreSql\PostgreSqlExperienceAcceptanceOwnerSource;
use DateTimeImmutable;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlExperienceAcceptanceOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlExperienceAcceptanceOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        $root = dirname(__DIR__, 3).'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'090_experience_acceptance_owner_source.down.sql'));
        $this->connection->exec((string) file_get_contents($root.'090_experience_acceptance_owner_source.sql'));
        $this->source = new PostgreSqlExperienceAcceptanceOwnerSource($this->connection, new ExperienceAcceptanceOwnerSourceMapper);
    }

    public function test_seven_streams_are_independent_idempotent_and_temporal(): void
    {
        $scope = new ExperienceAcceptanceScopeKey('experience:primary');
        foreach (self::streams() as [$stream, $decision]) {
            $state = self::state($stream, $decision, 1, '10:00:00', '10:00:01');
            self::assertSame(ExperienceAcceptanceWriteResult::Applied, $this->source->append($state));
            self::assertSame(ExperienceAcceptanceWriteResult::AlreadyApplied, $this->source->append($state));
            $read = $this->source->read($scope, $stream, new ExperienceAcceptanceObservedAt(self::at('11:00:00')));
            self::assertSame(ExperienceAcceptanceReadStatus::Found, $read->status);
            self::assertSame($decision, $read->revision?->decision);
        }
    }

    public function test_temporal_read_and_optimistic_locking_are_deterministic(): void
    {
        $stream = ExperienceAcceptanceStream::ResponsiveCompliance;
        self::assertSame(ExperienceAcceptanceWriteResult::Applied, $this->source->append(self::state($stream, 'available', 1, '10:00:00', '10:00:01')));
        self::assertSame(ExperienceAcceptanceWriteResult::VersionConflict, $this->source->append(self::state($stream, 'missing', 3, '10:20:00', '10:20:01')));
        self::assertSame(ExperienceAcceptanceWriteResult::Applied, $this->source->append(self::state($stream, 'missing', 2, '10:10:00', '10:10:01')));
        $scope = new ExperienceAcceptanceScopeKey('experience:primary');
        self::assertSame('available', $this->source->read($scope, $stream, new ExperienceAcceptanceObservedAt(self::at('10:05:00')))->revision?->decision);
        self::assertSame('missing', $this->source->read($scope, $stream, new ExperienceAcceptanceObservedAt(self::at('11:00:00')))->revision?->decision);
    }

    public function test_external_rollback_is_preserved(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(ExperienceAcceptanceWriteResult::Applied, $this->source->append(self::state(ExperienceAcceptanceStream::UserAcceptance, 'available', 1, '10:00:00', '10:00:01')));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        $read = $this->source->read(new ExperienceAcceptanceScopeKey('experience:primary'), ExperienceAcceptanceStream::UserAcceptance, new ExperienceAcceptanceObservedAt(self::at('11:00:00')));
        self::assertSame(ExperienceAcceptanceReadStatus::Missing, $read->status);
    }

    public function test_database_rejects_a_decision_outside_the_closed_catalogue(): void
    {
        $this->expectException(PDOException::class);
        $this->connection->exec("INSERT INTO experience_acceptance.owner_revision_journal(scope_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES ('experience:invalid','responsive_compliance',1,'degraded','2026-08-08T10:00:00Z','2026-08-08T10:00:01Z','".str_repeat('0', 64)."')");
    }

    /** @return list<array{ExperienceAcceptanceStream,string}> */
    private static function streams(): array
    {
        return [
            [ExperienceAcceptanceStream::ResponsiveCompliance, 'available'],
            [ExperienceAcceptanceStream::AccessibilityCompliance, 'available'],
            [ExperienceAcceptanceStream::UserExperience, 'available'],
            [ExperienceAcceptanceStream::EndToEndReadiness, 'available'],
            [ExperienceAcceptanceStream::PerformanceReadiness, 'available'],
            [ExperienceAcceptanceStream::UserAcceptance, 'available'],
            [ExperienceAcceptanceStream::ReleaseCandidate, 'available'],
        ];
    }

    private static function state(ExperienceAcceptanceStream $stream, string $decision, int $revision, string $effective, string $recorded): ExperienceAcceptanceRevisionState
    {
        return new ExperienceAcceptanceRevisionState('experience:primary', $stream, $revision, $decision, self::at($effective), self::at($recorded));
    }

    private static function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-08T'.$time.'.123456Z');
    }
}
