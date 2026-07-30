<?php

namespace Tests\PostgreSQL\LeadLifecycleContext;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppend;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualWriteResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualTransitionRepository;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlLeadLifecycleContextualTransitionTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_applied_idempotent_and_context_divergence(): void
    {
        $old = $this->old();
        $old->initialize($this->id(), LeadLifecycleState::Created);
        $repository = $this->repository($old);
        self::assertSame(LeadLifecycleContextualWriteResult::Applied, $repository->append($this->append('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')));
        self::assertSame(LeadLifecycleContextualWriteResult::AlreadyApplied, $repository->append($this->append('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')));
        self::assertSame(LeadLifecycleContextualWriteResult::ContextDivergence, $repository->append($this->append('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')));
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts')->fetchColumn());
    }

    public function test_external_transaction_rolls_back_transition_and_context(): void
    {
        $old = $this->old();
        $old->initialize($this->id(), LeadLifecycleState::Created);
        $this->connection->beginTransaction();
        $this->repository($old)->append($this->append('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
        $this->connection->rollBack();
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transitions')->fetchColumn());
        self::assertSame(0, (int) $this->connection->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts')->fetchColumn());
    }

    public function test_version_and_state_conflicts_are_closed(): void
    {
        $old = $this->old();
        $old->initialize($this->id(), LeadLifecycleState::Created);
        $r = $this->repository($old);
        $bad = new LeadLifecycleContextualAppend($this->id(), new LeadLifecycleTransition(LeadLifecycleState::Rejected, LeadLifecycleState::Closed, LeadLifecycleAction::Close), 1, $this->context('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
        self::assertSame(LeadLifecycleContextualWriteResult::StateConflict, $r->append($bad));
        $stale = new LeadLifecycleContextualAppend($this->id(), new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver), 2, $this->context('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
        self::assertSame(LeadLifecycleContextualWriteResult::VersionConflict, $r->append($stale));
    }

    public function test_missing_context_for_an_existing_contextual_transition_is_corrupted(): void
    {
        $old = $this->old();
        $old->initialize($this->id(), LeadLifecycleState::Created);
        $repository = $this->repository($old);
        self::assertSame(LeadLifecycleContextualWriteResult::Applied, $repository->append($this->append('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')));
        $this->connection->exec('DELETE FROM contacts_leads.lead_lifecycle_transition_contexts');
        self::assertSame(LeadLifecycleContextualWriteResult::Corrupted, $repository->append($this->append('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')));
    }

    public function test_local_transaction_commits_both_rows(): void
    {
        $old = $this->old();
        $old->initialize($this->id(), LeadLifecycleState::Created);
        self::assertSame(LeadLifecycleContextualWriteResult::Applied, $this->repository($old)->append($this->append('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')));
        self::assertSame(2, (int) $this->connection->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transitions')->fetchColumn());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts')->fetchColumn());
    }

    public function test_migration_024_and_rollback_are_isolated_and_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'024_lead_lifecycle_context.down.sql'));
        self::assertNull($this->connection->query("SELECT to_regclass('contacts_leads.lead_lifecycle_transition_contexts')")->fetchColumn());
        self::assertSame('contacts_leads.lead_lifecycle_transitions', $this->connection->query("SELECT to_regclass('contacts_leads.lead_lifecycle_transitions')")->fetchColumn());
        $this->connection->exec((string) file_get_contents($root.'024_lead_lifecycle_context.sql'));
        self::assertSame('contacts_leads.lead_lifecycle_transition_contexts', $this->connection->query("SELECT to_regclass('contacts_leads.lead_lifecycle_transition_contexts')")->fetchColumn());
    }

    public function test_multiprocess_identical_and_divergent_writes_leave_no_partial_state(): void
    {
        foreach ([['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', ['already_applied', 'applied']], ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', ['applied', 'context_divergence']]] as [$first,$second,$expected]) {
            PostgreSqlTestEnvironment::reset($this->connection);
            $id = LeadId::fromString('a4100000-0000-4000-8000-000000000092');
            $this->old()->initialize($id, LeadLifecycleState::Created);
            $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lead-context-'.bin2hex(random_bytes(6));
            $processes = [];
            foreach ([$first, $second] as $index => $actor) {
                $pipes = [];
                $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $index, $actor], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
                if (! is_resource($process)) {
                    throw new RuntimeException('Worker unavailable.');
                } $processes[] = [$process, $pipes];
            }
            while (! is_file($barrier.'.ready.0') || ! is_file($barrier.'.ready.1')) {
                usleep(1000);
            } touch($barrier.'.start');
            $results = [];
            foreach ($processes as [$process,$pipes]) {
                $results[] = trim(stream_get_contents($pipes[1]));
                $error = trim(stream_get_contents($pipes[2]));
                if (proc_close($process) !== 0 || $error !== '') {
                    throw new RuntimeException($error);
                }
            } sort($results);
            sort($expected);
            self::assertSame($expected, $results);
            self::assertSame(2, (int) $this->connection->query("SELECT count(*) FROM contacts_leads.lead_lifecycle_transitions WHERE lead_id='a4100000-0000-4000-8000-000000000092'")->fetchColumn());
            self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM contacts_leads.lead_lifecycle_transition_contexts WHERE lead_id='a4100000-0000-4000-8000-000000000092'")->fetchColumn());
            foreach (['.ready.0', '.ready.1', '.start'] as $suffix) {
                @unlink($barrier.$suffix);
            }
        }
    }

    private function old(): PostgreSqlLeadLifecycleWorkflowRepository
    {
        return new PostgreSqlLeadLifecycleWorkflowRepository($this->connection, new LeadLifecycleWorkflowMapper);
    }

    private function repository(PostgreSqlLeadLifecycleWorkflowRepository $old): PostgreSqlLeadLifecycleContextualTransitionRepository
    {
        return new PostgreSqlLeadLifecycleContextualTransitionRepository($this->connection, $old, new LeadLifecycleWorkflowMapper, new LeadLifecycleContextMapper);
    }

    private function id(): LeadId
    {
        return LeadId::fromString('a4100000-0000-4000-8000-000000000091');
    }

    private function append(string $actor): LeadLifecycleContextualAppend
    {
        return new LeadLifecycleContextualAppend($this->id(), new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver), 1, $this->context($actor));
    }

    private function context(string $actor): LeadLifecycleTransitionContext
    {
        return new LeadLifecycleTransitionContext(LeadLifecycleActorId::fromString($actor), LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-21T12:00:00+00:00')));
    }
}
