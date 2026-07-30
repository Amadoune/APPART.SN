<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflow;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\Contract\AdministrativeActionLifecycleWorkflowStore;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionLifecycleRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class AdministrativeActionLifecycleRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_complete_graph_is_lazy_unique_and_resolved_by_laravel(): void
    {
        $components = [
            AdministrativeActionLifecycleWorkflow::class,
            AdministrativeActionLifecycleWorkflowMapper::class,
            AdministrativeActionEnrollmentCanonicalizer::class,
            PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction::class,
            PostgreSqlAdministrativeActionLifecycleRepository::class,
            AdministrativeActionLifecycleWorkflowStore::class,
        ];
        foreach ($components as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $workflow = $this->app->make(AdministrativeActionLifecycleWorkflow::class);
        $mapper = $this->app->make(AdministrativeActionLifecycleWorkflowMapper::class);
        $canonicalizer = $this->app->make(AdministrativeActionEnrollmentCanonicalizer::class);
        $transaction = $this->app->make(PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction::class);
        $repository = $this->app->make(PostgreSqlAdministrativeActionLifecycleRepository::class);
        $store = $this->app->make(AdministrativeActionLifecycleWorkflowStore::class);

        self::assertSame($workflow, $this->app->make(AdministrativeActionLifecycleWorkflow::class));
        self::assertSame($mapper, $this->app->make(AdministrativeActionLifecycleWorkflowMapper::class));
        self::assertSame($canonicalizer, $this->app->make(AdministrativeActionEnrollmentCanonicalizer::class));
        self::assertSame($transaction, $this->app->make(PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction::class));
        self::assertSame($repository, $store);
        self::assertSame($repository, $this->app->make(PostgreSqlAdministrativeActionLifecycleRepository::class));
        self::assertSame($mapper, new \ReflectionProperty($repository, 'mapper')->getValue($repository));
        self::assertSame($canonicalizer, new \ReflectionProperty($repository, 'canonicalizer')->getValue($repository));
        self::assertSame($transaction, new \ReflectionProperty($repository, 'transaction')->getValue($repository));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($repository, 'connection')->getValue($repository));
    }

    public function test_runtime_health_is_healthy_at_forty_seven_without_sql_or_transaction(): void
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
