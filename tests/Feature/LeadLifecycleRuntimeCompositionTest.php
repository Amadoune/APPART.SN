<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\Contract\LeadLifecycleWorkflowStore;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class LeadLifecycleRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_complete_graph_is_lazy_unique_and_resolved_by_laravel(): void
    {
        $components = [
            LeadLifecycleWorkflow::class,
            LeadLifecycleWorkflowMapper::class,
            PostgreSqlLeadLifecycleWorkflowRepository::class,
            LeadLifecycleWorkflowStore::class,
        ];
        foreach ($components as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $workflow = $this->app->make(LeadLifecycleWorkflow::class);
        $mapper = $this->app->make(LeadLifecycleWorkflowMapper::class);
        $repository = $this->app->make(PostgreSqlLeadLifecycleWorkflowRepository::class);
        $store = $this->app->make(LeadLifecycleWorkflowStore::class);

        self::assertSame($workflow, $this->app->make(LeadLifecycleWorkflow::class));
        self::assertSame($repository, $store);
        self::assertSame($repository, $this->app->make(PostgreSqlLeadLifecycleWorkflowRepository::class));
        self::assertSame($mapper, new \ReflectionProperty($repository, 'mapper')->getValue($repository));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($repository, 'connection')->getValue($repository));
        self::assertStringNotContainsStringIgnoringCase('fake', $repository::class);
        self::assertStringNotContainsStringIgnoringCase('null', $repository::class);
    }

    public function test_runtime_health_reports_thirty_two_capabilities_as_healthy_without_business_execution(): void
    {
        $connection = $this->app->make(PDO::class);
        self::assertFalse($connection->inTransaction());

        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
        self::assertFalse($connection->inTransaction());
    }
}
