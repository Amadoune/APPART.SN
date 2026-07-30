<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicProjectionSourceLookupArchitectureTest extends TestCase
{
    public function test_lookup_only_resolves_and_classifies_without_execution_or_pagination(): void
    {
        $file = dirname(__DIR__, 2).'/app/Infrastructure/PublicProjectionSourceLookup/CertifiedPublicProjectionSourceLookup.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        foreach (['PublicProjectionUpdateExecutor', 'PublicListingProjectionUpdater', 'PropertyListingsResolver', 'MultiTargetPropagationStrategy', 'readPage(', 'update(', 'now(', 'CURRENT_TIMESTAMP', 'Http', 'Laravel'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
        self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents);
    }

    public function test_all_runtime_source_statuses_are_mapped_explicitly(): void
    {
        $statuses = file_get_contents(dirname(__DIR__, 2).'/app/Application/ProjectionRuntimeSource/ProjectionSourceAssemblyStatus.php');
        $lookup = file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/PublicProjectionSourceLookup/CertifiedPublicProjectionSourceLookup.php');
        self::assertIsString($statuses);
        self::assertIsString($lookup);
        preg_match_all('/case\s+(\w+)\s*=/', $statuses, $matches);
        foreach ($matches[1] as $status) {
            self::assertStringContainsString('ProjectionSourceAssemblyStatus::'.$status, $lookup);
        }
    }
}
