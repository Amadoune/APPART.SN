<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AddressIdentityArchitectureTest extends TestCase
{
    public function test_issuer_is_a_pure_real_estate_catalog_application_authority(): void
    {
        $root = dirname(__DIR__, 2);
        $directory = $root.'/src/Modules/RealEstateCatalog/Application/AddressIdentity';
        $files = array_merge(glob($directory.'/*.php') ?: [], glob($directory.'/Contract/*.php') ?: []);
        $source = implode("\n", array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        self::assertStringContainsString('namespace Appart\\Modules\\RealEstateCatalog\\Application\\AddressIdentity', $source);
        self::assertStringContainsString("private const string NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8'", $source);
        self::assertStringContainsString("private const string NAME_PREFIX = 'https://appart.sn/real-estate-catalog/address-intents/v1/'", $source);
        foreach (['PDO', 'SQL', 'Repository', 'Ledger', 'Clock', 'random', 'Geography', 'PropertyAuthoring', 'Projection', 'Search', 'Illuminate', 'Http'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_existing_address_domain_objects_and_change_address_are_not_modified_by_f2(): void
    {
        $root = dirname(__DIR__, 2);
        $addressId = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Domain/ValueObject/AddressId.php');
        $changeAddress = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Application/UseCase/ChangeAddress.php');

        self::assertStringNotContainsString('AddressIntentId', $addressId);
        self::assertStringNotContainsString('AddressIdentityIssuer', $changeAddress);
    }
}
