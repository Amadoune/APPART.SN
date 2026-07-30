<?php

namespace Tests\PostgreSQL\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Domain\Exception\ConcurrentAdministrativeActionModification;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionRepository;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\AdministrationAudit\FakeAdministrativeActionRegistryHarness;

final class PostgreSqlAdministrativeActionRepositoryIntegrationTest extends TestCase
{
    private PDO $connection;

    private FakeAdministrativeActionRegistryHarness $fixtures;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->fixtures = new FakeAdministrativeActionRegistryHarness;
    }

    public function test_add_commits_root_and_complete_children(): void
    {
        $repository = $this->repository();
        $action = $this->fixtures->approvedAction();

        $repository->add($action);

        self::assertSame(1, $this->countRows('administrative_actions'));
        self::assertSame(1, $this->countRows('administrative_action_approvals'));
        self::assertSame(1, $this->countRows('administrative_action_decisions'));
        self::assertSame(2, $this->countRows('administrative_action_audit_entries'));
    }

    public function test_add_rolls_back_root_and_children_after_controlled_failure(): void
    {
        $transaction = new FailBeforeCommitAdministrativeActionTransaction($this->connection);
        $repository = new PostgreSqlAdministrativeActionRepository($this->connection, new AdministrativeActionMapper, $transaction);
        $transaction->failNext();

        try {
            $repository->add($this->fixtures->approvedAction());
            self::fail('The transaction must fail before commit.');
        } catch (ConcurrentAdministrativeActionModification) {
            self::assertSame(0, $this->countRows('administrative_actions'));
            self::assertSame(0, $this->countRows('administrative_action_approvals'));
            self::assertSame(0, $this->countRows('administrative_action_decisions'));
            self::assertSame(0, $this->countRows('administrative_action_audit_entries'));
        }
    }

    public function test_save_rolls_back_root_and_new_proofs_after_controlled_failure(): void
    {
        $transaction = new FailBeforeCommitAdministrativeActionTransaction($this->connection);
        $repository = new PostgreSqlAdministrativeActionRepository($this->connection, new AdministrativeActionMapper, $transaction);
        $action = $this->fixtures->minimalAction();
        $repository->add($action);
        $loaded = $repository->find($action->id());
        self::assertNotNull($loaded);
        $this->fixtures->mutateWithEvent($loaded);
        $transaction->failNext();

        try {
            $repository->save($loaded, 0);
            self::fail('The transaction must fail before commit.');
        } catch (ConcurrentAdministrativeActionModification) {
            $stored = $repository->find($action->id());
            self::assertNotNull($stored);
            self::assertSame(0, $stored->version());
            self::assertNull($stored->reason());
            self::assertSame(0, $this->countRows('administrative_action_audit_entries'));
        }
    }

    public function test_database_rejects_an_orphan_audit_entry(): void
    {
        $this->expectException(PDOException::class);
        $this->connection->exec("INSERT INTO administration_audit.administrative_action_audit_entries (action_id, sequence, fact, actor_id, reason, recorded_at) VALUES ('31000000-0000-4000-8000-000000000099', 1, 'orphan_entry', 'actor:test', 'This orphan must be rejected.', '2026-07-17T10:00:00+00:00')");
    }

    public function test_repository_refuses_regressive_or_unchanged_version(): void
    {
        $repository = $this->repository();
        $action = $this->fixtures->minimalAction();
        $repository->add($action);

        $this->expectException(ConcurrentAdministrativeActionModification::class);
        $repository->save($action, 0);
    }

    public function test_database_rejects_unknown_status(): void
    {
        $this->expectException(PDOException::class);
        $this->connection->exec("INSERT INTO administration_audit.administrative_actions (id, author_id, target_id, action_type, requires_four_eyes, last_changed_at, status, version) VALUES ('31000000-0000-4000-8000-000000000098', 'actor:test', 'identity:test', 'profile_note', false, '2026-07-17T10:00:00+00:00', 'unknown', 0)");
    }

    private function repository(): PostgreSqlAdministrativeActionRepository
    {
        return new PostgreSqlAdministrativeActionRepository($this->connection, new AdministrativeActionMapper);
    }

    private function countRows(string $table): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM administration_audit.'.$table)->fetchColumn();
    }
}
