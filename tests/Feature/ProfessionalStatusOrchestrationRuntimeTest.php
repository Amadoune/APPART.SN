<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\Contract\ProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\DeterministicProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualTransitionStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualTransitionRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ProfessionalStatusOrchestrationRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_graph_is_unique_lazy_and_shares_certified_implementations(): void
    {
        foreach ([ProfessionalStatusContextualTransitionStore::class, PostgreSqlProfessionalStatusContextualTransitionRepository::class, ProfessionalStatusContextualReplayInspector::class, PostgreSqlProfessionalStatusContextualReplayInspector::class, ProfessionalStatusOrchestrator::class, DeterministicProfessionalStatusOrchestrator::class] as $component) {
            self::assertTrue($this->app->bound($component));
            self::assertFalse($this->app->resolved($component));
        }

        self::assertSame($this->app->make(PostgreSqlProfessionalStatusContextualTransitionRepository::class), $this->app->make(ProfessionalStatusContextualTransitionStore::class));
        self::assertSame($this->app->make(PostgreSqlProfessionalStatusContextualReplayInspector::class), $this->app->make(ProfessionalStatusContextualReplayInspector::class));
        self::assertSame($this->app->make(DeterministicProfessionalStatusOrchestrator::class), $this->app->make(ProfessionalStatusOrchestrator::class));
    }

    public function test_runtime_health_is_healthy_at_thirty_eight_without_execution(): void
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
