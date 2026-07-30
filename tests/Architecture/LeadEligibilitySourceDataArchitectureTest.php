<?php

namespace Tests\Architecture;

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterialization;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterializationResult;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceRecord;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LeadEligibilitySourceDataArchitectureTest extends TestCase
{
    public function test_contract_reuses_only_historical_enums_and_revision(): void
    {
        $contract = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/LeadEligibilitySourceData/LeadEligibilityMaterialization.php');
        foreach (['ListingContactability', 'AdvertiserEligibility', 'EligibilityRevision'] as $required) {
            self::assertStringContainsString($required, $contract);
        }
        foreach (['ListingEligibilityProof', 'AdvertiserEligibilityProof', 'LeadEligibilityProof', 'enum '] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contract);
        }
        self::assertSame(
            ['created', 'already_materialized', 'stale_version', 'divergent_version', 'incoherent_revision', 'relation_divergence', 'continuity_conflict'],
            array_column(LeadEligibilityMaterializationResult::cases(), 'value'),
        );
    }

    public function test_models_are_final_readonly_and_contain_no_generated_value(): void
    {
        foreach ([LeadEligibilityMaterialization::class, LeadEligibilitySourceRecord::class] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal());
            self::assertTrue($reflection->isReadOnly());
        }
    }

    public function test_infrastructure_contains_no_domain_reconstruction_or_forbidden_coupling(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Infrastructure/Persistence';
        $files = [
            $root.'/LeadEligibilitySourceDataMapper.php',
            $root.'/PostgreSql/PostgreSqlLeadEligibilityDecisionStore.php',
            $root.'/PostgreSql/Migrations/023_lead_eligibility_source_data.sql',
        ];
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['listing_revisions', 'actor_id', 'ListingRegistry', 'ListingRepository', 'Domain\\Model', 'LeadEligibilityProof', 'ListingCatalog', 'AdvertiserCatalog', 'Http', 'Outbox', 'Worker', 'Consumer', 'Projection', 'now()', 'clock_timestamp', 'gen_random_uuid', 'uuid_generate'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_source_data_runtime_binding_remains_unique_after_catalog_adapter_progression(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(LeadEligibilitySourceDataMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlLeadEligibilityDecisionStore::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlLeadEligibilityDecisionStore::class, LeadEligibilityDecisionMaterializer::class)'));
        self::assertSame(1, substr_count($provider, 'alias(MaterializedListingCatalog::class, ListingCatalog::class)'));
        self::assertSame(1, substr_count($provider, 'alias(MaterializedAdvertiserCatalog::class, AdvertiserCatalog::class)'));
        foreach (['->materialize(', '->current(', '->history(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }

        $requirements = (string) file_get_contents($root.'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringContainsString('RuntimeHealthComponent::LeadEligibilityDecisionMaterializer, LeadEligibilityDecisionMaterializer::class', $requirements);
        self::assertStringNotContainsString('PostgreSqlLeadEligibilityDecisionStore', $requirements);
    }
}
