<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ListingPublicationRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_complete_graph_is_lazy_unique_and_resolved_by_laravel(): void
    {
        $components = [
            ListingPublicationWorkflow::class,
            ListingPublicationWorkflowMapper::class,
            PostgreSqlListingPublicationWorkflowRepository::class,
            ListingPublicationWorkflowStore::class,
        ];
        foreach ($components as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $workflow = $this->app->make(ListingPublicationWorkflow::class);
        $mapper = $this->app->make(ListingPublicationWorkflowMapper::class);
        $repository = $this->app->make(PostgreSqlListingPublicationWorkflowRepository::class);
        $store = $this->app->make(ListingPublicationWorkflowStore::class);

        self::assertSame($workflow, $this->app->make(ListingPublicationWorkflow::class));
        self::assertSame($repository, $store);
        self::assertSame($mapper, new \ReflectionProperty($repository, 'mapper')->getValue($repository));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($repository, 'connection')->getValue($repository));
        self::assertStringNotContainsStringIgnoringCase('fake', $repository::class);
        self::assertStringNotContainsStringIgnoringCase('null', $repository::class);
    }

    public function test_runtime_health_reports_the_complete_graph_as_healthy(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }

    public function test_orchestrator_is_a_lazy_unique_production_alias_with_certified_dependencies(): void
    {
        foreach ([DeterministicListingPublicationOrchestrator::class, ListingPublicationOrchestrator::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $implementation = $this->app->make(DeterministicListingPublicationOrchestrator::class);
        $orchestrator = $this->app->make(ListingPublicationOrchestrator::class);

        self::assertSame($implementation, $orchestrator);
        self::assertSame($this->app->make(ListingPublicationWorkflow::class), new \ReflectionProperty($implementation, 'workflow')->getValue($implementation));
        self::assertSame($this->app->make(ListingPublicationWorkflowStore::class), new \ReflectionProperty($implementation, 'store')->getValue($implementation));
        self::assertStringNotContainsStringIgnoringCase('fake', $implementation::class);
    }
}
