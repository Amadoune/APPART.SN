<?php

namespace Tests\Feature;

use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Application\PropertyListingAuthoringRuntime\PropertyListingAuthoringRuntimeStatus;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\AuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingDraftStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingOwnershipStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingDraftStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingOwnershipStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyAuthoringStore;
use PDO;
use ReflectionProperty;
use Tests\TestCase;

final class PropertyListingAuthoringRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(PDO::class, static fn (): PDO => new PDO('sqlite::memory:'));
    }

    public function test_owner_providers_resolve_lazy_singletons_sharing_the_runtime_connection(): void
    {
        foreach ([
            PropertyAuthoringStore::class,
            ListingDraftStore::class,
            ListingOwnershipStore::class,
            AuthoringPortfolioStore::class,
        ] as $contract) {
            self::assertFalse($this->app->resolved($contract));
        }

        $runtime = $this->app->make(PropertyListingAuthoringRuntimeV1::class);

        self::assertInstanceOf(PostgreSqlPropertyAuthoringStore::class, $runtime->propertyAuthoring());
        self::assertInstanceOf(PostgreSqlListingDraftStore::class, $runtime->listingDraft());
        self::assertInstanceOf(PostgreSqlListingOwnershipStore::class, $runtime->listingOwnership());
        self::assertInstanceOf(PostgreSqlAuthoringPortfolioStore::class, $runtime->authoringPortfolio());

        foreach ([
            $runtime->propertyAuthoring(),
            $runtime->listingDraft(),
            $runtime->listingOwnership(),
            $runtime->authoringPortfolio(),
        ] as $store) {
            self::assertSame(
                $this->app->make(PDO::class),
                (new ReflectionProperty($store, 'connection'))->getValue($store),
            );
        }
    }

    public function test_runtime_is_ready_without_extending_frozen_runtime_health(): void
    {
        $runtime = $this->app->make(PropertyListingAuthoringRuntimeV1::class);

        self::assertSame(PropertyListingAuthoringRuntimeStatus::Ready, $runtime->inspect()->status);
        self::assertNull($runtime->inspect()->code);
        self::assertSame($runtime, $this->app->make(PropertyListingAuthoringRuntimeV1::class));
        self::assertCount(60, PublicProjectionRuntimeRequirements::certified());
        self::assertFalse($this->app->make(PDO::class)->inTransaction());
    }
}
