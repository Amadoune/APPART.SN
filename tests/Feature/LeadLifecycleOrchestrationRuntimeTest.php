<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\Contract\LeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\DeterministicLeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualTransitionStore;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualTransitionRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class LeadLifecycleOrchestrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_graph_is_unique_lazy_and_healthy(): void
    {
        self::assertSame($this->app->make(DeterministicLeadLifecycleOrchestrator::class), $this->app->make(LeadLifecycleOrchestrator::class));
        self::assertSame($this->app->make(PostgreSqlLeadLifecycleContextualTransitionRepository::class), $this->app->make(LeadLifecycleContextualTransitionStore::class));
        self::assertSame($this->app->make(PostgreSqlLeadLifecycleContextualReplayInspector::class), $this->app->make(LeadLifecycleContextualReplayInspector::class));
        $report = $this->app->make(RuntimeHealthInspector::class)->inspect();
        self::assertSame(RuntimeHealthStatus::Healthy, $report->status);
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
    }
}
