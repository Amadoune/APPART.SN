<?php

namespace Tests\PostgreSQL\ProfessionalMandateOwnerSource;

use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceState;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceStatus;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceWriteResult;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalMandateOwnerSource;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalMandateOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlProfessionalMandateOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlProfessionalMandateOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->source = new PostgreSqlProfessionalMandateOwnerSource(
            $this->connection,
            new ProfessionalMandateOwnerSourceMapper,
        );
    }

    #[Test]
    public function source_resolves_zero_one_and_multiple_professionals_deterministically(): void
    {
        self::assertSame(ProfessionalMandateOwnerSourceStatus::NotMandated, $this->source->resolve($this->id(1))->status);

        self::assertSame(ProfessionalMandateOwnerSourceWriteResult::Applied, $this->source->replace(
            $this->state(1, [$this->id(101)], 1, 11, 'a'),
            0,
        ));
        $resolved = $this->source->resolve($this->id(1));
        self::assertSame(ProfessionalMandateOwnerSourceStatus::Resolved, $resolved->status);
        self::assertSame($this->id(101), $resolved->professionalId?->value);

        self::assertSame(ProfessionalMandateOwnerSourceWriteResult::Applied, $this->source->replace(
            $this->state(1, [$this->id(101), $this->id(102)], 2, 12, 'b'),
            1,
        ));
        self::assertSame(ProfessionalMandateOwnerSourceStatus::Ambiguous, $this->source->resolve($this->id(1))->status);
    }

    #[Test]
    public function writes_are_idempotent_versioned_and_divergence_safe(): void
    {
        $state = $this->state(2, [$this->id(201)], 1, 21, 'c');
        self::assertSame(ProfessionalMandateOwnerSourceWriteResult::Applied, $this->source->replace($state, 0));
        self::assertSame(ProfessionalMandateOwnerSourceWriteResult::AlreadyApplied, $this->source->replace($state, 0));
        self::assertSame(
            ProfessionalMandateOwnerSourceWriteResult::DivergentIntent,
            $this->source->replace($this->state(2, [$this->id(202)], 1, 21, 'd'), 0),
        );
        self::assertSame(
            ProfessionalMandateOwnerSourceWriteResult::VersionConflict,
            $this->source->replace($this->state(2, [$this->id(202)], 2, 22, 'e'), 0),
        );
        self::assertSame($this->id(201), $this->source->resolve($this->id(2))->professionalId?->value);
    }

    #[Test]
    public function owner_source_participates_safely_in_an_enclosing_transaction(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(
            ProfessionalMandateOwnerSourceWriteResult::Applied,
            $this->source->replace($this->state(3, [$this->id(301)], 1, 31, 'f'), 0),
        );
        $this->connection->rollBack();

        self::assertSame(ProfessionalMandateOwnerSourceStatus::NotMandated, $this->source->resolve($this->id(3))->status);
        self::assertFalse($this->connection->inTransaction());
    }

    /** @param list<string> $professionalIds */
    private function state(int $account, array $professionalIds, int $version, int $intent, string $checksum): ProfessionalMandateOwnerSourceState
    {
        return new ProfessionalMandateOwnerSourceState(
            $this->id($account),
            $professionalIds,
            $version,
            $this->id($intent),
            str_repeat($checksum, 64),
            new DateTimeImmutable('2026-07-28T12:00:00+00:00'),
        );
    }

    private function id(int $suffix): string
    {
        return sprintf('63000000-0000-4000-8000-%012d', $suffix);
    }
}
