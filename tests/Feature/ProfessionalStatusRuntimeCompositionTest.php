<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusWorkflowRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ProfessionalStatusRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_complete_graph_is_lazy_unique_and_resolved_by_laravel(): void
    {
        $components = [
            ProfessionalStatusWorkflow::class,
            ProfessionalStatusWorkflowMapper::class,
            PostgreSqlProfessionalStatusWorkflowRepository::class,
            ProfessionalStatusWorkflowStore::class,
        ];

        foreach ($components as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $workflow = $this->app->make(ProfessionalStatusWorkflow::class);
        $mapper = $this->app->make(ProfessionalStatusWorkflowMapper::class);
        $repository = $this->app->make(PostgreSqlProfessionalStatusWorkflowRepository::class);
        $store = $this->app->make(ProfessionalStatusWorkflowStore::class);

        self::assertSame($workflow, $this->app->make(ProfessionalStatusWorkflow::class));
        self::assertSame($mapper, $this->app->make(ProfessionalStatusWorkflowMapper::class));
        self::assertSame($repository, $store);
        self::assertSame($repository, $this->app->make(PostgreSqlProfessionalStatusWorkflowRepository::class));
        self::assertSame($mapper, new \ReflectionProperty($repository, 'mapper')->getValue($repository));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($repository, 'connection')->getValue($repository));
        self::assertStringNotContainsStringIgnoringCase('fake', $repository::class);
        self::assertStringNotContainsStringIgnoringCase('null', $repository::class);
    }

    public function test_runtime_health_reports_thirty_seven_capabilities_without_business_execution(): void
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
