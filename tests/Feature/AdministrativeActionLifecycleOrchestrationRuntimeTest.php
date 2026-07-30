<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\Contract\AdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\DeterministicAdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualTransitionStore;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualTransitionRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class AdministrativeActionLifecycleOrchestrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_runtime_graph_is_unique_lazy_and_shares_exact_instances(): void
    {
        foreach ([AdministrativeActionContextualTransitionStore::class, AdministrativeActionContextualReplayInspector::class, AdministrativeActionLifecycleOrchestrator::class] as $contract) {
            self::assertTrue($this->app->bound($contract));
            self::assertFalse($this->app->resolved($contract));
        }

        self::assertSame($this->app->make(PostgreSqlAdministrativeActionContextualTransitionRepository::class), $this->app->make(AdministrativeActionContextualTransitionStore::class));
        self::assertSame($this->app->make(PostgreSqlAdministrativeActionContextualReplayInspector::class), $this->app->make(AdministrativeActionContextualReplayInspector::class));
        self::assertSame($this->app->make(DeterministicAdministrativeActionLifecycleOrchestrator::class), $this->app->make(AdministrativeActionLifecycleOrchestrator::class));
    }

    public function test_runtime_health_is_healthy_at_forty_eight_without_execution(): void
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
