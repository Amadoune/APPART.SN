<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ContentSeoDecisionReadArchitectureTest extends TestCase
{
    public function test_snapshot_contracts_are_framework_and_infrastructure_agnostic(): void
    {
        foreach (['Application/Snapshot', 'Application/Contract'] as $relative) {
            foreach (glob(dirname(__DIR__, 2).'/src/Modules/ContentSeo/'.$relative.'/*.php') ?: [] as $file) {
                if (! str_contains(basename($file), 'ContentSeo')) {
                    continue;
                }
                $contents = file_get_contents($file);
                self::assertIsString($contents);
                self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|\\\\Infrastructure\\\\|\bPDO\b|\bSQL\b|Http|Runtime)/i', $contents, $file);
            }
        }
    }

    public function test_reader_contains_no_seo_recalculation(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/PostgreSqlContentSeoSourceSnapshotReader.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringContainsString('WHERE listing_id=:listing_id', $contents);
        self::assertDoesNotMatchRegularExpression('/(?:CanonicalPolicy|ListingSeoDecisionPolicy|robots|slug|json.?ld|Search)/i', $contents);
    }

    public function test_results_have_no_default_branch(): void
    {
        foreach (glob(dirname(__DIR__, 2).'/src/Modules/ContentSeo/Application/Snapshot/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }
}
