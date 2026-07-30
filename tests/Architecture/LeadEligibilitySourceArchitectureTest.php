<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadEligibilitySourceArchitectureTest extends TestCase
{
    public function test_adapters_depend_exclusively_on_historical_contracts_reader_and_evidence(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Infrastructure/EligibilitySource';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(2, $files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            self::assertStringContainsString('LeadEligibilitySourceDataReader', $contents);
            foreach (['PDO', 'PostgreSql', 'Repository', 'Domain\\Model', 'listing_revisions', 'actor_id', 'LeadEligibilityDecisionMaterializer', 'LeadEligibilityProof', 'ListingLifecycleWorkflow', 'Http', 'Outbox', 'Worker', 'Consumer', 'Projection'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_mapping_is_mechanical_and_exhaustive_without_recalculation(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Infrastructure/EligibilitySource';
        $listing = (string) file_get_contents($root.'/MaterializedListingCatalog.php');
        $advertiser = (string) file_get_contents($root.'/MaterializedAdvertiserCatalog.php');
        self::assertStringContainsString('$record->listingDecision', $listing);
        self::assertStringContainsString('$record->revision', $listing);
        self::assertStringContainsString('$record->advertiserDecision', $advertiser);
        self::assertStringContainsString('$record->revision', $advertiser);
        foreach (['Contactable', 'NotPublished', 'Closed', 'EligibleRecipient', 'Suspended', 'NotListingRecipient'] as $decision) {
            self::assertStringNotContainsString($decision, $listing.$advertiser);
        }
        self::assertStringNotContainsString('default', $listing.$advertiser);
    }

    public function test_runtime_bindings_and_health_are_unique_lazy_and_structural(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlLeadEligibilityDecisionStore::class, LeadEligibilitySourceDataReader::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(MaterializedListingCatalog::class)'));
        self::assertSame(1, substr_count($provider, 'alias(MaterializedListingCatalog::class, ListingCatalog::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(MaterializedAdvertiserCatalog::class)'));
        self::assertSame(1, substr_count($provider, 'alias(MaterializedAdvertiserCatalog::class, AdvertiserCatalog::class)'));
        foreach (['->contactabilityOf(', '->eligibilityFor(', '->current(', '->materialize(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }

        $requirements = (string) file_get_contents($root.'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringContainsString('RuntimeHealthComponent::ListingCatalog, ListingCatalog::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::AdvertiserCatalog, AdvertiserCatalog::class', $requirements);
        self::assertStringNotContainsString('MaterializedListingCatalog', $requirements);
        self::assertStringNotContainsString('MaterializedAdvertiserCatalog', $requirements);
    }

    public function test_no_new_contract_enum_proof_migration_or_storage_is_introduced(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertCount(3, glob($root.'/src/Modules/ContactsLeads/Application/Contract/*.php') ?: []);
        self::assertSame([], glob($root.'/src/Modules/ContactsLeads/Infrastructure/EligibilitySource/*Migration*') ?: []);
        self::assertSame([], glob($root.'/src/Modules/ContactsLeads/Infrastructure/EligibilitySource/*Store*') ?: []);
        self::assertSame([], glob($root.'/src/Modules/ContactsLeads/Infrastructure/EligibilitySource/*Proof*') ?: []);
    }
}
