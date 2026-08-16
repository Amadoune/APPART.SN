<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicPropertyPromotionArchitectureTest extends TestCase
{
    public function test_promotion_is_an_application_orchestrator_without_forbidden_backflow(): void
    {
        $root = dirname(__DIR__, 2);
        $runtime = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Application/Promotion/DeterministicPromoteAuthoredPropertyV1.php');
        $contract = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Application/Promotion/Contract/PromoteAuthoredPropertyV1.php');

        self::assertStringContainsString('RegisterProperty', $runtime);
        self::assertStringContainsString('PropertyAuthoringStore', $runtime);
        self::assertStringContainsString('PromotionTransaction', $runtime);
        foreach (['PDO', 'SELECT ', 'INSERT ', 'UPDATE ', 'DELETE ', 'Projection', 'Search', 'PublicListing'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $runtime.$contract);
        }
    }

    public function test_certified_domain_contracts_remain_unchanged_and_migration_is_additive(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/100_property_promotion.sql');
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS real_estate_catalog.property_promotion_commands', $migration);
        foreach (['ALTER TABLE', 'UPDATE ', 'DELETE FROM', 'real_estate_catalog_authoring.property_authoring'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $migration);
        }
        self::assertStringNotContainsString('Promotion', (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Application/UseCase/RegisterProperty.php'));
        self::assertStringNotContainsString('Promotion', (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Domain/Policy/PropertyTypePolicy.php'));
    }

    public function test_canonical_address_identity_is_local_to_promotion_compatibility(): void
    {
        $root = dirname(__DIR__, 2);
        $promotion = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Application/Promotion/DeterministicPromoteAuthoredPropertyV1.php');
        $address = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Domain/Model/Address.php');
        $property = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Domain/Model/Property.php');

        self::assertStringContainsString('addressesCompatible', $promotion);
        self::assertStringContainsString('$existing->id->equals($expected->id)', $promotion);
        self::assertStringContainsString('$this->placeId->equals($other->placeId)', $address);
        self::assertStringContainsString('$this->line->equals($other->line)', $address);
        self::assertStringNotContainsString('$this->id->equals($other->id)', $address);
        self::assertStringContainsString('$this->address?->equals($address)', $property);
        self::assertFileDoesNotExist($root.'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/101_property_promotion.sql');
    }
}
