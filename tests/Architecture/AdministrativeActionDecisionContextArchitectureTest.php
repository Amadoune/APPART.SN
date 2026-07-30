<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionDecisionContextArchitectureTest extends TestCase
{
    public function test_the_contract_has_no_infrastructure_or_runtime_dependency(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/AdministrativeActionDecisionContext';
        $files = glob($directory.'/*.php');
        self::assertIsArray($files);
        self::assertNotEmpty($files);

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertStringNotContainsString('Illuminate\\', $contents);
            self::assertStringNotContainsString('PDO', $contents);
            self::assertStringNotContainsString('PostgreSql', $contents);
            self::assertStringNotContainsString('Workflow', $contents);
            self::assertStringNotContainsString('Repository', $contents);
        }
    }

    public function test_no_runtime_persistence_or_migration_is_part_of_the_sprint(): void
    {
        $roadmap = file_get_contents(dirname(__DIR__, 2).'/docs/PHASE-4.7-DECISION-CONTEXT-CERTIFICATION.md');
        self::assertIsString($roadmap);
        self::assertStringContainsString('aucun Workflow', $roadmap);
        self::assertStringContainsString('aucune migration', $roadmap);
        self::assertStringContainsString('aucun binding Runtime', $roadmap);
    }
}
