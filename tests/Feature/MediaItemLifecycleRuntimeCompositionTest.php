<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\Contract\MediaItemLifecycleWorkflowStore;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleWorkflowRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class MediaItemLifecycleRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_complete_graph_is_lazy_unique_and_resolved_by_laravel(): void
    {
        $components = [MediaItemLifecycleWorkflow::class, MediaItemLifecycleWorkflowMapper::class, PostgreSqlMediaItemLifecycleWorkflowRepository::class, MediaItemLifecycleWorkflowStore::class];
        foreach ($components as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $workflow = $this->app->make(MediaItemLifecycleWorkflow::class);
        $mapper = $this->app->make(MediaItemLifecycleWorkflowMapper::class);
        $repository = $this->app->make(PostgreSqlMediaItemLifecycleWorkflowRepository::class);
        $store = $this->app->make(MediaItemLifecycleWorkflowStore::class);

        self::assertSame($workflow, $this->app->make(MediaItemLifecycleWorkflow::class));
        self::assertSame($mapper, $this->app->make(MediaItemLifecycleWorkflowMapper::class));
        self::assertSame($repository, $store);
        self::assertSame($repository, $this->app->make(PostgreSqlMediaItemLifecycleWorkflowRepository::class));
        self::assertSame($mapper, new \ReflectionProperty($repository, 'mapper')->getValue($repository));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($repository, 'connection')->getValue($repository));
    }

    public function test_runtime_health_is_healthy_at_forty_two_without_sql_or_transaction(): void
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
