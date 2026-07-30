<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaOwnershipLookupArchitectureTest extends TestCase
{
    public function test_application_contract_is_framework_and_infrastructure_agnostic(): void
    {
        $path = dirname(__DIR__, 2).'/src/Modules/Media/Application/Ownership';
        foreach (glob($path.'/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|\\\\Infrastructure\\\\|\bPDO\b|\bSQL\b|Http|Runtime)/i', $contents, $file);
        }
    }

    public function test_postgresql_lookup_is_bounded_and_uses_only_the_persisted_property_relation(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaCollectionOwnershipLookup.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringContainsString('property_id=:property_id', $contents);
        self::assertStringContainsString('LIMIT 2', $contents);
        self::assertDoesNotMatchRegularExpression('/(?:LIKE|ILIKE|regexp|http|Listing)/i', $contents);
    }

    public function test_resolution_is_exhaustive_without_default_branch(): void
    {
        $path = dirname(__DIR__, 2).'/src/Modules/Media/Application/Ownership';
        foreach (glob($path.'/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }
}
