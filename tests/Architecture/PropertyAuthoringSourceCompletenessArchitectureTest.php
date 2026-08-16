<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyAuthoringSourceCompletenessArchitectureTest extends TestCase
{
    public function test_f4_composes_replay_validator_without_domain_promotion_or_cross_module_reads(): void
    {
        $root = dirname(__DIR__, 2);
        $enricher = (string) file_get_contents($root.'/app/Application/PropertyAuthoringSourceCompleteness/DeterministicPropertyAuthoringStateEnricherV1.php');
        $operations = (string) file_get_contents($root.'/app/Application/PropertyListingAuthoringOperations/DeterministicPropertyListingAuthoringOperations.php');

        self::assertStringContainsString('GeographySelectionReplayValidatorV1', $enricher);
        self::assertStringContainsString('PropertyAuthoringStateEnricherV1', $operations);
        foreach (['RegisterProperty', 'PropertyRegistry::add', 'AddressIdentityIssuerV1', 'BusinessYear', 'PropertyTypePolicy', 'Projection', 'Search'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $enricher);
        }
    }

    public function test_migration_is_099_additive_nullable_and_has_a_dedicated_rollback(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/';
        $up = (string) file_get_contents($root.'099_property_authoring_source_completeness.sql');
        $down = (string) file_get_contents($root.'099_property_authoring_source_completeness.down.sql');

        foreach (['property_reference', 'surface_square_meters', 'rooms', 'bathrooms', 'construction_year', 'geographic_place_id', 'address_line', 'address_intent_id'] as $column) {
            self::assertStringContainsString('ADD COLUMN IF NOT EXISTS '.$column, $up);
            self::assertStringContainsString('DROP COLUMN IF EXISTS '.$column, $down);
        }
        self::assertStringNotContainsString('UPDATE ', $up);
        self::assertStringNotContainsString('NOT NULL', $up);
    }

    public function test_workspace_transports_owner_sources_and_never_accepts_server_authorities(): void
    {
        $root = dirname(__DIR__, 2);
        $view = (string) file_get_contents($root.'/resources/views/authoring-workspace.blade.php');
        $script = (string) file_get_contents($root.'/resources/js/authoring.js');
        $request = (string) file_get_contents($root.'/app/Http/Requests/PublicAuthoringJourneyHttpRequest.php');

        foreach (['propertyReference', 'surfaceSquareMeters', 'rooms', 'bathrooms', 'constructionYear', 'geographicPlaceId', 'addressLine'] as $field) {
            self::assertStringContainsString($field, $view.$script);
        }
        self::assertStringNotContainsString('name="addressIntentId"', $view);
        self::assertStringNotContainsString("'addressIntentId' =>", $request);
        self::assertStringNotContainsString("'ownerAccountId' =>", $request);
    }
}
