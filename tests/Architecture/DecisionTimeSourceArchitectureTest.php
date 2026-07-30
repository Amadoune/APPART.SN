<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class DecisionTimeSourceArchitectureTest extends TestCase
{
    public function test_application_contract_has_no_runtime_or_infrastructure_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/DecisionTimeSource';
        $files = [...(glob($root.'/*.php') ?: []), ...(glob($root.'/Contract/*.php') ?: [])];
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|Infrastructure|\bPDO\b|Http|Runtime)/i', $contents);
        }
    }

    public function test_production_source_never_uses_an_execution_clock_or_persistence(): void
    {
        foreach (glob(dirname(__DIR__, 2).'/app/Infrastructure/DecisionTimeSource/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:\bnow\s*\(|CURRENT_TIMESTAMP|clock_timestamp|new\s+DateTime|\bPDO\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b)/i', $contents);
        }
    }

    public function test_owner_snapshot_contains_decision_time_in_its_certified_checksum_payload(): void
    {
        $mapper = file_get_contents(dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/ContentSeoSourceSnapshotMapper.php');
        self::assertIsString($mapper);
        self::assertStringContainsString("'decision_at' => \$this->format(\$snapshot->decisionAt)", $mapper);
        self::assertStringContainsString("hash('sha256', \$payload)", $mapper);
    }
}
