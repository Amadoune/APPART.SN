<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\ContactsLeads\Application\Contract\AdvertiserCatalog;
use Appart\Modules\ContactsLeads\Application\Contract\ListingCatalog;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilitySourceDataReader;
use Appart\Modules\ContactsLeads\Infrastructure\EligibilitySource\MaterializedAdvertiserCatalog;
use Appart\Modules\ContactsLeads\Infrastructure\EligibilitySource\MaterializedListingCatalog;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadEligibilityDecisionStore;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class LeadEligibilitySourceRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_both_catalog_graphs_are_unique_lazy_and_reader_only(): void
    {
        foreach ([LeadEligibilitySourceDataReader::class, MaterializedListingCatalog::class, ListingCatalog::class, MaterializedAdvertiserCatalog::class, AdvertiserCatalog::class] as $component) {
            self::assertTrue($this->app->bound($component));
            self::assertFalse($this->app->resolved($component));
        }

        $reader = $this->app->make(LeadEligibilitySourceDataReader::class);
        $listing = $this->app->make(ListingCatalog::class);
        $advertiser = $this->app->make(AdvertiserCatalog::class);

        self::assertSame($this->app->make(PostgreSqlLeadEligibilityDecisionStore::class), $reader);
        self::assertSame($this->app->make(MaterializedListingCatalog::class), $listing);
        self::assertSame($this->app->make(MaterializedAdvertiserCatalog::class), $advertiser);
        self::assertSame($reader, new \ReflectionProperty($listing, 'reader')->getValue($listing));
        self::assertSame($reader, new \ReflectionProperty($advertiser, 'reader')->getValue($advertiser));
    }

    public function test_runtime_health_is_healthy_at_thirty_two_without_read_or_transaction(): void
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
