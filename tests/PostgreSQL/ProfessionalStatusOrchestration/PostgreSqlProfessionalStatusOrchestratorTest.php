<?php

namespace Tests\PostgreSQL\ProfessionalStatusOrchestration;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\DeterministicProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusTransitionRequest;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusExpectedVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusReplayPolicy;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualTransitionRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusWorkflowRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusContextMapper;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use DateTimeImmutable;
use PDO;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PostgreSqlProfessionalStatusOrchestratorTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlProfessionalStatusWorkflowRepository $historical;

    private DeterministicProfessionalStatusOrchestrator $orchestrator;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $workflowMapper = new ProfessionalStatusWorkflowMapper;
        $contextMapper = new ProfessionalStatusContextMapper;
        $this->historical = new PostgreSqlProfessionalStatusWorkflowRepository($this->connection, $workflowMapper);
        $store = new PostgreSqlProfessionalStatusContextualTransitionRepository($this->connection, $this->historical, $workflowMapper, $contextMapper);
        $inspector = new PostgreSqlProfessionalStatusContextualReplayInspector($this->connection, $workflowMapper, $contextMapper);
        $this->orchestrator = new DeterministicProfessionalStatusOrchestrator($store, $inspector, new ProfessionalStatusReplayPolicy, new ProfessionalStatusWorkflow);
    }

    public function test_complete_sequence_applies_replays_and_rejects_context_divergence(): void
    {
        $id = $this->id();
        $this->historical->initialize($id);
        self::assertSame(ProfessionalStatusOrchestrationStatus::Applied, $this->orchestrator->execute($this->request($id, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'))->status);
        self::assertSame(ProfessionalStatusOrchestrationStatus::AlreadyApplied, $this->orchestrator->execute($this->request($id, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'))->status);
        self::assertSame(ProfessionalStatusOrchestrationStatus::ContextDivergence, $this->orchestrator->execute($this->request($id, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'))->status);
        self::assertSame(2, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transitions WHERE professional_id='".$id->value."'")->fetchColumn());
        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transition_contexts WHERE professional_id='".$id->value."'")->fetchColumn());
    }

    public function test_denied_decision_persists_nothing(): void
    {
        $id = ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000047');
        $this->historical->initialize($id);
        $request = new ProfessionalStatusTransitionRequest($id, ProfessionalStatusAction::Reactivate, $this->context('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
        self::assertSame(ProfessionalStatusOrchestrationStatus::Denied, $this->orchestrator->execute($request)->status);
        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transitions WHERE professional_id='".$id->value."'")->fetchColumn());
        self::assertSame(0, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transition_contexts WHERE professional_id='".$id->value."'")->fetchColumn());
    }

    public function test_concurrent_identical_commands_converge_without_partial_state(): void
    {
        $id = ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000046');
        $this->historical->initialize($id);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'professional-orchestrator-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start professional status orchestration worker.');
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
        self::assertSame(2, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transitions WHERE professional_id='".$id->value."'")->fetchColumn());
        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transition_contexts WHERE professional_id='".$id->value."'")->fetchColumn());
    }

    private function request(ProfessionalStatusId $id, string $actor): ProfessionalStatusTransitionRequest
    {
        return new ProfessionalStatusTransitionRequest($id, ProfessionalStatusAction::Suspend, $this->context($actor));
    }

    private function context(string $actor): ProfessionalStatusTransitionContext
    {
        return new ProfessionalStatusTransitionContext(ProfessionalStatusActorId::fromString($actor), ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00')), new ProfessionalStatusExpectedVersion(1));
    }

    private function id(): ProfessionalStatusId
    {
        return ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000045');
    }
}
