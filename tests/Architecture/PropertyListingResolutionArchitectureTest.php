<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyListingResolutionArchitectureTest extends TestCase
{
    public function test_application_contract_has_no_infrastructure_or_runtime_dependency(): void
    {
        $path = dirname(__DIR__, 2).'/app/Application/PropertyListingResolution';
        foreach (glob($path.'/{,Contract/}*.php', GLOB_BRACE) ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', '\\Infrastructure\\', 'PDO', 'Http', 'Delivery', 'Runtime'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_postgresql_adapter_is_read_only_keyset_bounded_and_property_scoped(): void
    {
        $file = dirname(__DIR__, 2).'/app/Infrastructure/PropertyListingResolution/PostgreSql/PostgreSqlPropertyListingsResolver.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringContainsString('property_id = CAST(:property_id AS uuid)', $contents);
        self::assertStringContainsString('id > CAST(:after_id AS uuid)', $contents);
        self::assertStringContainsString('ORDER BY id LIMIT ', $contents);
        self::assertStringContainsString('$limit + 1', $contents);
        self::assertDoesNotMatchRegularExpression('/\b(?:INSERT|UPDATE|DELETE)\b/', $contents);
    }
}
