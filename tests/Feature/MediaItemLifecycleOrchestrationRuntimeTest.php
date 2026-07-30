<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualTransitionStore;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\Contract\MediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\DeterministicMediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualTransitionRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class MediaItemLifecycleOrchestrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_graph_is_unique_lazy_and_shares_certified_implementations(): void
    {
        foreach ([
            MediaItemLifecycleContextualTransitionStore::class,
            PostgreSqlMediaItemLifecycleContextualTransitionRepository::class,
            MediaItemLifecycleContextualReplayInspector::class,
            PostgreSqlMediaItemLifecycleContextualReplayInspector::class,
            MediaItemLifecycleOrchestrator::class,
            DeterministicMediaItemLifecycleOrchestrator::class,
        ] as $component) {
            self::assertTrue($this->app->bound($component));
            self::assertFalse($this->app->resolved($component));
        }

        self::assertSame($this->app->make(PostgreSqlMediaItemLifecycleContextualTransitionRepository::class), $this->app->make(MediaItemLifecycleContextualTransitionStore::class));
        self::assertSame($this->app->make(PostgreSqlMediaItemLifecycleContextualReplayInspector::class), $this->app->make(MediaItemLifecycleContextualReplayInspector::class));
        self::assertSame($this->app->make(DeterministicMediaItemLifecycleOrchestrator::class), $this->app->make(MediaItemLifecycleOrchestrator::class));
    }

    public function test_runtime_health_is_healthy_at_forty_three_without_execution(): void
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
