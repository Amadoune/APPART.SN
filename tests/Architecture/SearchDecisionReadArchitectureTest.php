<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class SearchDecisionReadArchitectureTest extends TestCase
{
    public function test_application_decision_contracts_are_framework_and_infrastructure_agnostic(): void
    {
        foreach (['Application/Decision', 'Application/Contract'] as $relative) {
            $path = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/'.$relative;
            foreach (glob($path.'/*.php') ?: [] as $file) {
                if (! str_contains(basename($file), 'SearchDecision')) {
                    continue;
                }
                $contents = file_get_contents($file);
                self::assertIsString($contents);
                self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|\\\\Infrastructure\\\\|\bPDO\b|\bSQL\b|Http|Runtime)/i', $contents, $file);
            }
        }
    }

    public function test_reader_is_targeted_and_contains_no_search_decision_logic(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/PostgreSqlSearchDecisionReader.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringContainsString('WHERE listing_id=:listing_id', $contents);
        self::assertDoesNotMatchRegularExpression('/(?:SearchRank::fromInt|SearchProjection::derived|score|eligib|ranking|http)/i', $contents);
    }

    public function test_read_and_write_results_are_exhaustive_without_default_branch(): void
    {
        $path = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/Decision';
        foreach (glob($path.'/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_writer_serializes_first_insert_by_listing_before_row_lock_and_upsert(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/PostgreSqlSearchDecisionWriter.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);

        $advisoryLock = strpos($contents, 'pg_advisory_xact_lock(hashtextextended(:listing_id,0))');
        $rowLock = strpos($contents, 'WHERE listing_id=:listing_id FOR UPDATE');
        $upsert = strpos($contents, 'ON CONFLICT(listing_id) DO UPDATE');
        self::assertIsInt($advisoryLock);
        self::assertIsInt($rowLock);
        self::assertIsInt($upsert);
        self::assertLessThan($rowLock, $advisoryLock);
        self::assertLessThan($upsert, $rowLock);
    }
}
