<?php

namespace Tests\PostgreSQL\LeadLifecycleOrchestration;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\DeterministicLeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleTransitionRequest;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualTransitionRepository;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlLeadLifecycleOrchestratorTest extends TestCase
{
    private PDO $pdo;

    private PostgreSqlLeadLifecycleWorkflowRepository $historical;

    private DeterministicLeadLifecycleOrchestrator $orchestrator;

    protected function setUp(): void
    {
        $this->pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->pdo);
        PostgreSqlTestEnvironment::reset($this->pdo);
        $mapper = new LeadLifecycleWorkflowMapper;
        $contextMapper = new LeadLifecycleContextMapper;
        $this->historical = new PostgreSqlLeadLifecycleWorkflowRepository($this->pdo, $mapper);
        $store = new PostgreSqlLeadLifecycleContextualTransitionRepository($this->pdo, $this->historical, $mapper, $contextMapper);
        $this->orchestrator = new DeterministicLeadLifecycleOrchestrator($store, new PostgreSqlLeadLifecycleContextualReplayInspector($this->pdo, $mapper, $contextMapper), new LeadLifecycleWorkflow);
    }

    public function test_applied_replay_context_divergence_and_version_conflict(): void
    {
        $this->historical->initialize($this->id(), LeadLifecycleState::Created);
        self::assertSame(LeadLifecycleOrchestrationStatus::Applied, $this->orchestrator->execute($this->request('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 1))->status);
        self::assertSame(LeadLifecycleOrchestrationStatus::AlreadyApplied, $this->orchestrator->execute($this->request('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 1))->status);
        self::assertSame(LeadLifecycleOrchestrationStatus::ContextDivergence, $this->orchestrator->execute($this->request('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 1))->status);
        self::assertSame(LeadLifecycleOrchestrationStatus::VersionConflict, $this->orchestrator->execute($this->request('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 7))->status);
    }

    public function test_denied_never_writes(): void
    {
        $this->historical->initialize($this->id(), LeadLifecycleState::Created);
        $request = new LeadLifecycleTransitionRequest($this->id(), LeadLifecycleAction::Close, 1, $this->context('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
        self::assertSame(LeadLifecycleOrchestrationStatus::Denied, $this->orchestrator->execute($request)->status);
        self::assertSame(1, (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transitions')->fetchColumn());
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts')->fetchColumn());
    }

    public function test_multiprocess_convergence_is_deterministic(): void
    {
        foreach ([[['deliver', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'], ['deliver', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'], ['already_applied', 'applied']], [['deliver', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'], ['deliver', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'], ['applied', 'context_divergence']], [['deliver', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'], ['reject', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'], ['applied', 'state_conflict']]] as [$one,$two,$expected]) {
            PostgreSqlTestEnvironment::reset($this->pdo);
            $id = LeadId::fromString('a4100000-0000-4000-8000-000000000097');
            $this->historical->initialize($id, LeadLifecycleState::Created);
            $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lead-orch-'.bin2hex(random_bytes(6));
            $processes = [];
            foreach ([$one, $two] as $index => $input) {
                $pipes = [];
                $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $index, $input[0], $input[1]], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
                if (! is_resource($process)) {
                    throw new RuntimeException('Worker unavailable.');
                }$processes[] = [$process, $pipes];
            }
            while (! is_file($barrier.'.ready.0') || ! is_file($barrier.'.ready.1')) {
                usleep(1000);
            }touch($barrier.'.start');
            $actual = [];
            foreach ($processes as [$process,$pipes]) {
                $actual[] = trim(stream_get_contents($pipes[1]));
                $error = trim(stream_get_contents($pipes[2]));
                if (proc_close($process) !== 0 || $error !== '') {
                    throw new RuntimeException($error);
                }
            }sort($actual);
            sort($expected);
            self::assertSame($expected, $actual);
            self::assertSame(2, (int) $this->pdo->query("SELECT count(*) FROM contacts_leads.lead_lifecycle_transitions WHERE lead_id='a4100000-0000-4000-8000-000000000097'")->fetchColumn());
            self::assertSame(1, (int) $this->pdo->query("SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts WHERE lead_id='a4100000-0000-4000-8000-000000000097'")->fetchColumn());
            foreach (['.ready.0', '.ready.1', '.start'] as $suffix) {
                @unlink($barrier.$suffix);
            }
        }
    }

    private function request(string $actor, int $version): LeadLifecycleTransitionRequest
    {
        return new LeadLifecycleTransitionRequest($this->id(), LeadLifecycleAction::Deliver, $version, $this->context($actor));
    }

    private function context(string $actor): LeadLifecycleTransitionContext
    {
        return new LeadLifecycleTransitionContext(LeadLifecycleActorId::fromString($actor), LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00')));
    }

    private function id(): LeadId
    {
        return LeadId::fromString('a4100000-0000-4000-8000-000000000095');
    }
}
