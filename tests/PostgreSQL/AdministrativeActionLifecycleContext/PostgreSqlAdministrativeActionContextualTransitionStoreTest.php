<?php

namespace Tests\PostgreSQL\AdministrativeActionLifecycleContext;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualAppend;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualInspectionStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionExpectedVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualTransitionStore;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionTransitionContextMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualTransitionRepository;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionLifecycleRepository;
use DateTimeImmutable;
use PDO;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\AdministrativeActionContextualTransitionStoreContract;

final class PostgreSqlAdministrativeActionContextualTransitionStoreTest extends AdministrativeActionContextualTransitionStoreContract
{
    private PDO $pdo;

    private PostgreSqlAdministrativeActionLifecycleRepository $lifecycle;

    private PostgreSqlAdministrativeActionContextualTransitionRepository $contextual;

    private PostgreSqlAdministrativeActionContextualReplayInspector $replayInspector;

    protected function setUp(): void
    {
        $this->pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->pdo);
        $workflowMapper = new AdministrativeActionLifecycleWorkflowMapper;
        $this->lifecycle = new PostgreSqlAdministrativeActionLifecycleRepository($this->pdo, $workflowMapper, new AdministrativeActionEnrollmentCanonicalizer);
        $this->contextual = new PostgreSqlAdministrativeActionContextualTransitionRepository($this->pdo, $this->lifecycle, new AdministrativeActionTransitionContextMapper);
        $this->replayInspector = new PostgreSqlAdministrativeActionContextualReplayInspector($this->pdo, $workflowMapper);
        parent::setUp();
    }

    protected function store(): AdministrativeActionContextualTransitionStore
    {
        return $this->contextual;
    }

    protected function inspector(): AdministrativeActionContextualReplayInspector
    {
        return $this->replayInspector;
    }

    protected function resetPersistence(): void
    {
        PostgreSqlTestEnvironment::reset($this->pdo);
    }

    protected function seedEnrolled(): void
    {
        $statement = $this->pdo->prepare('INSERT INTO administration_audit.administrative_actions (id,author_id,target_id,action_type,requires_four_eyes,last_changed_at,status,reason,version) VALUES(CAST(:id AS uuid),:author,:target,:type,false,:changed,:status,:reason,1)');
        $statement->execute([
            'id' => $this->id()->value,
            'author' => 'author-001',
            'target' => 'identity:context-target',
            'type' => 'context_contract',
            'changed' => '2026-07-24T09:00:00.000000+00:00',
            'status' => 'draft',
            'reason' => $this->reason()->value,
        ]);
        $checkpoint = (new AdministrativeActionEnrollmentCanonicalizer)->checkpoint($this->id(), 1, AdministrativeActionLifecycleState::Draft);
        $this->lifecycle->enroll($checkpoint);
    }

    protected function validAppend(): AdministrativeActionContextualAppend
    {
        return $this->appendAt('2026-07-24T10:00:00.000000+00:00');
    }

    protected function divergentAppend(): AdministrativeActionContextualAppend
    {
        return $this->appendAt('2026-07-24T10:01:00.000000+00:00');
    }

    public function test_external_transaction_rolls_back_journal_context_and_historical_mirror(): void
    {
        $this->pdo->beginTransaction();
        self::assertSame(AdministrativeActionContextualWriteResult::Applied, $this->contextual->append($this->validAppend()));
        $this->pdo->rollBack();

        self::assertSame(1, $this->rowCount('administrative_action_lifecycle_transitions'));
        self::assertSame(0, $this->rowCount('administrative_action_lifecycle_transition_contexts'));
        self::assertSame('draft', $this->pdo->query("SELECT status FROM administration_audit.administrative_actions WHERE id='".$this->id()->value."'")->fetchColumn());
    }

    public function test_inspector_returns_corrupted_when_persisted_context_is_tampered(): void
    {
        $this->contextual->append($this->validAppend());
        $this->pdo->exec("UPDATE administration_audit.administrative_action_lifecycle_transition_contexts SET historical_reason='Different persisted historical reason.' WHERE action_id='".$this->id()->value."'");
        self::assertSame(AdministrativeActionContextualInspectionStatus::Corrupted, $this->replayInspector->inspectLatest($this->id())->status);
    }

    public function test_migration_035_and_rollback_are_isolated_from_034(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->pdo->exec((string) file_get_contents($root.'035_administrative_action_lifecycle_context.down.sql'));
        self::assertSame('administration_audit.administrative_action_lifecycle_transitions', $this->pdo->query("SELECT to_regclass('administration_audit.administrative_action_lifecycle_transitions')::text")->fetchColumn());
        self::assertNull($this->pdo->query("SELECT to_regclass('administration_audit.administrative_action_lifecycle_transition_contexts')")->fetchColumn());
        $this->pdo->exec((string) file_get_contents($root.'035_administrative_action_lifecycle_context.sql'));
    }

    public function test_concurrent_identical_appends_converge_without_partial_state(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'admin-context-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start administrative context worker.');
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
        self::assertSame(2, $this->rowCount('administrative_action_lifecycle_transitions'));
        self::assertSame(1, $this->rowCount('administrative_action_lifecycle_transition_contexts'));
    }

    private function appendAt(string $occurredAt): AdministrativeActionContextualAppend
    {
        $actor = ActorId::fromString('author-001');
        $decision = new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Present,
            AdministrativeActionDecisionAuthority::directRecording($actor, $actor),
        );

        return new AdministrativeActionContextualAppend(
            $this->id(),
            new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::Draft, AdministrativeActionLifecycleState::Recorded, AdministrativeActionLifecycleAction::Record),
            AdministrativeActionTransitionExecutionContext::record(
                new AdministrativeActionExpectedVersion(1),
                $actor,
                AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable($occurredAt)),
                $this->reason(),
                $decision,
            ),
        );
    }

    private function id(): AdministrativeActionId
    {
        return AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000035');
    }

    private function reason(): AuditReason
    {
        return AuditReason::fromString('Administrative contextual persistence reason.');
    }

    private function rowCount(string $table): int
    {
        return (int) $this->pdo->query("SELECT count(*) FROM administration_audit.$table WHERE action_id='".$this->id()->value."'")->fetchColumn();
    }
}
