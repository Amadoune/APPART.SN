<?php

namespace Tests\PostgreSQL\AdministrativeActionLifecyclePersistence;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\AdministrativeActionLifecyclePersistenceReadStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\Contract\AdministrativeActionLifecycleWorkflowStore;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentCheckpoint;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleSourceChecksum;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionLifecycleRepository;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionRepository;
use DateTimeImmutable;
use PDO;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\AdministrativeActionLifecycleWorkflowStoreContract;

final class PostgreSqlAdministrativeActionLifecycleWorkflowStoreTest extends AdministrativeActionLifecycleWorkflowStoreContract
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->pdo);
        parent::setUp();
    }

    protected function store(): AdministrativeActionLifecycleWorkflowStore
    {
        return new PostgreSqlAdministrativeActionLifecycleRepository(
            $this->pdo,
            new AdministrativeActionLifecycleWorkflowMapper,
            new AdministrativeActionEnrollmentCanonicalizer,
        );
    }

    protected function seedDraft(bool $requiresFourEyes): AdministrativeActionLifecycleEnrollmentCheckpoint
    {
        $statement = $this->pdo->prepare('INSERT INTO administration_audit.administrative_actions (id, author_id, target_id, action_type, requires_four_eyes, last_changed_at, status, reason, version) VALUES (CAST(:id AS uuid), :author_id, :target_id, :action_type, :requires_four_eyes, :last_changed_at, :status, :reason, :version)');
        $statement->bindValue('id', $this->actionId()->value);
        $statement->bindValue('author_id', $this->author()->value);
        $statement->bindValue('target_id', 'identity:contract-account');
        $statement->bindValue('action_type', 'role_assignment_review');
        $statement->bindValue('requires_four_eyes', $requiresFourEyes, PDO::PARAM_BOOL);
        $statement->bindValue('last_changed_at', '2026-07-17T10:01:00.000000+00:00');
        $statement->bindValue('status', 'draft');
        $statement->bindValue('reason', $this->reason()->value);
        $statement->bindValue('version', 1, PDO::PARAM_INT);
        $statement->execute();

        return (new AdministrativeActionEnrollmentCanonicalizer)->checkpoint(
            $this->actionId(),
            1,
            AdministrativeActionLifecycleState::Draft,
        );
    }

    protected function resetStore(): void
    {
        PostgreSqlTestEnvironment::reset($this->pdo);
    }

    public function test_approve_updates_journal_and_complete_historical_mirror(): void
    {
        $checkpoint = $this->seedDraft(true);
        $store = $this->store();
        $store->enroll($checkpoint);
        $record = AdministrativeActionHistoricalMirrorMutation::record(
            $checkpoint->actionId,
            $checkpoint->historicalVersion,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::Draft,
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleAction::Record,
            ),
            $this->author(),
            $this->reason(),
            $this->occurredAt(),
        );
        self::assertSame(AdministrativeActionHistoricalMirrorWriteResult::Applied, $store->append($record));

        $approve = AdministrativeActionHistoricalMirrorMutation::approve(
            $checkpoint->actionId,
            $checkpoint->historicalVersion + 1,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleState::Approved,
                AdministrativeActionLifecycleAction::Approve,
            ),
            ActorId::fromString('actor:contract-reviewer'),
            AuditReason::fromString('Fixed independent decision evidence for the contract.'),
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-17T10:03:00Z')),
            ApprovalId::fromString('31000000-0000-4000-8000-000000000101'),
            DecisionId::fromString('31000000-0000-4000-8000-000000000102'),
        );
        self::assertSame(AdministrativeActionHistoricalMirrorWriteResult::Applied, $store->append($approve));

        $historical = (new PostgreSqlAdministrativeActionRepository($this->pdo, new AdministrativeActionMapper))->find($checkpoint->actionId);
        self::assertNotNull($historical);
        self::assertSame('approved', $historical->status()->value);
        self::assertSame(3, $historical->version());
        self::assertCount(2, $historical->auditEntries());
        self::assertNotNull($historical->approval());
        self::assertNotNull($historical->decision());
    }

    public function test_reject_updates_journal_and_complete_historical_mirror(): void
    {
        $checkpoint = $this->seedDraft(true);
        $store = $this->store();
        $store->enroll($checkpoint);
        $store->append(AdministrativeActionHistoricalMirrorMutation::record(
            $checkpoint->actionId,
            1,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::Draft,
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleAction::Record,
            ),
            $this->author(),
            $this->reason(),
            $this->occurredAt(),
        ));
        $reject = AdministrativeActionHistoricalMirrorMutation::reject(
            $checkpoint->actionId,
            2,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleState::Rejected,
                AdministrativeActionLifecycleAction::Reject,
            ),
            ActorId::fromString('actor:contract-reviewer'),
            AuditReason::fromString('Fixed independent decision evidence for the contract.'),
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-17T10:03:00Z')),
            DecisionId::fromString('31000000-0000-4000-8000-000000000103'),
        );

        self::assertSame(AdministrativeActionHistoricalMirrorWriteResult::Applied, $store->append($reject));
        $historical = (new PostgreSqlAdministrativeActionRepository($this->pdo, new AdministrativeActionMapper))->find($checkpoint->actionId);
        self::assertNotNull($historical);
        self::assertSame('rejected', $historical->status()->value);
        self::assertNull($historical->approval());
        self::assertNotNull($historical->decision());
    }

    public function test_missing_historical_source_is_not_enrolled(): void
    {
        $checkpoint = new AdministrativeActionLifecycleEnrollmentCheckpoint(
            $this->actionId(),
            0,
            AdministrativeActionLifecycleState::Draft,
            AdministrativeActionLifecycleSourceChecksum::fromString(str_repeat('a', 64)),
        );

        self::assertSame(AdministrativeActionLifecycleEnrollmentResult::SourceMissing, $this->store()->enroll($checkpoint));
    }

    public function test_local_failure_rolls_back_journal_and_partial_historical_children(): void
    {
        $checkpoint = $this->seedDraft(true);
        $store = $this->store();
        $store->enroll($checkpoint);
        $store->append(AdministrativeActionHistoricalMirrorMutation::record(
            $checkpoint->actionId,
            1,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::Draft,
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleAction::Record,
            ),
            $this->author(),
            $this->reason(),
            $this->occurredAt(),
        ));
        $this->pdo->exec("INSERT INTO administration_audit.administrative_action_decisions (action_id, decision_id, outcome, decided_by, reason, decided_at) VALUES ('31000000-0000-4000-8000-000000000001', '31000000-0000-4000-8000-000000000102', 'approved', 'actor:contract-reviewer', 'Preexisting divergent decision.', '2026-07-17T10:03:00Z')");

        $approve = AdministrativeActionHistoricalMirrorMutation::approve(
            $checkpoint->actionId,
            2,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleState::Approved,
                AdministrativeActionLifecycleAction::Approve,
            ),
            ActorId::fromString('actor:contract-reviewer'),
            AuditReason::fromString('Fixed independent decision evidence for the contract.'),
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-17T10:03:00Z')),
            ApprovalId::fromString('31000000-0000-4000-8000-000000000101'),
            DecisionId::fromString('31000000-0000-4000-8000-000000000102'),
        );

        self::assertSame(AdministrativeActionHistoricalMirrorWriteResult::PersistenceCorrupted, $store->append($approve));
        self::assertSame(2, (int) $this->pdo->query('SELECT count(*) FROM administration_audit.administrative_action_lifecycle_transitions')->fetchColumn());
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM administration_audit.administrative_action_approvals')->fetchColumn());
        self::assertSame('pending_approval', $this->pdo->query("SELECT status FROM administration_audit.administrative_actions WHERE id = '31000000-0000-4000-8000-000000000001'")->fetchColumn());
    }

    public function test_external_transaction_rollback_removes_transition_and_mirror_mutation(): void
    {
        $checkpoint = $this->seedDraft(false);
        $store = $this->store();
        $store->enroll($checkpoint);
        $this->pdo->beginTransaction();
        self::assertSame(
            AdministrativeActionHistoricalMirrorWriteResult::Applied,
            $store->append($this->recordMutation($checkpoint, $this->reason(), $this->occurredAt())),
        );
        $this->pdo->rollBack();

        self::assertSame(AdministrativeActionLifecycleState::Draft, $store->read($checkpoint->actionId)->snapshot?->state);
        $historical = (new PostgreSqlAdministrativeActionRepository($this->pdo, new AdministrativeActionMapper))->find($checkpoint->actionId);
        self::assertNotNull($historical);
        self::assertSame('draft', $historical->status()->value);
        self::assertSame(1, $historical->version());
        self::assertCount(0, $historical->auditEntries());
    }

    public function test_corrupted_journal_row_is_reported_without_exception(): void
    {
        $checkpoint = $this->seedDraft(false);
        $this->store()->enroll($checkpoint);
        $this->pdo->exec("UPDATE administration_audit.administrative_action_lifecycle_transitions SET entry_checksum = '".str_repeat('0', 64)."'");

        self::assertSame(
            AdministrativeActionLifecyclePersistenceReadStatus::Corrupted,
            $this->store()->read($checkpoint->actionId)->status,
        );
    }

    public function test_migration_and_rollback_are_isolated(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/';
        $down = file_get_contents($root.'034_administrative_action_lifecycle_workflow.down.sql');
        self::assertIsString($down);
        $this->pdo->exec($down);
        self::assertNull($this->pdo->query("SELECT to_regclass('administration_audit.administrative_action_lifecycle_transitions')")->fetchColumn());
        self::assertSame('administration_audit.administrative_actions', $this->pdo->query("SELECT to_regclass('administration_audit.administrative_actions')::text")->fetchColumn());
        $up = file_get_contents($root.'034_administrative_action_lifecycle_workflow.sql');
        self::assertIsString($up);
        $this->pdo->exec($up);
    }

    public function test_concurrent_identical_transitions_converge_without_partial_state(): void
    {
        $checkpoint = $this->seedDraft(false);
        $this->store()->enroll($checkpoint);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'administrative-action-lifecycle-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Administrative Action Lifecycle worker.');
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
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(2, (int) $this->pdo->query("SELECT count(*) FROM administration_audit.administrative_action_lifecycle_transitions WHERE action_id = '31000000-0000-4000-8000-000000000001'")->fetchColumn());
        self::assertSame(1, (int) $this->pdo->query("SELECT count(*) FROM administration_audit.administrative_action_audit_entries WHERE action_id = '31000000-0000-4000-8000-000000000001'")->fetchColumn());
        self::assertSame('recorded', $this->pdo->query("SELECT status FROM administration_audit.administrative_actions WHERE id = '31000000-0000-4000-8000-000000000001'")->fetchColumn());
    }
}
