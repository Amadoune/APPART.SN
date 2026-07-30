<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilityDecisionMaterializer;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadEligibilitySourceDataMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadEligibilityDecisionStore;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class LeadEligibilitySourceDataRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_materializer_graph_is_unique_lazy_and_uses_runtime_pdo(): void
    {
        foreach ([LeadEligibilitySourceDataMapper::class, PostgreSqlLeadEligibilityDecisionStore::class, LeadEligibilityDecisionMaterializer::class] as $component) {
            self::assertTrue($this->app->bound($component));
            self::assertFalse($this->app->resolved($component));
        }

        $mapper = $this->app->make(LeadEligibilitySourceDataMapper::class);
        $store = $this->app->make(PostgreSqlLeadEligibilityDecisionStore::class);
        $materializer = $this->app->make(LeadEligibilityDecisionMaterializer::class);

        self::assertSame($store, $materializer);
        self::assertSame($store, $this->app->make(PostgreSqlLeadEligibilityDecisionStore::class));
        self::assertSame($mapper, new \ReflectionProperty($store, 'mapper')->getValue($store));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($store, 'connection')->getValue($store));
        self::assertStringNotContainsStringIgnoringCase('fake', $store::class);
        self::assertStringNotContainsStringIgnoringCase('null', $store::class);
    }

    public function test_runtime_health_is_healthy_at_thirty_two_capabilities_without_transaction(): void
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
