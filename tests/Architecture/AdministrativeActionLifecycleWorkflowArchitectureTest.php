<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleWorkflowArchitectureTest extends TestCase
{
    public function test_the_workflow_has_only_the_certified_context_dependency(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/AdministrativeActionLifecycle';
        $files = glob($directory.'/*.php');
        self::assertIsArray($files);
        self::assertCount(7, $files);

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertStringNotContainsString('Domain\\Model\\AdministrativeAction', $contents);
            self::assertStringNotContainsString('FourEyesPolicy', $contents);
            self::assertStringNotContainsString('Illuminate\\', $contents);
            self::assertStringNotContainsString('PDO', $contents);
            self::assertStringNotContainsString('Repository', $contents);
            self::assertStringNotContainsString('Infrastructure\\', $contents);
        }
    }

    public function test_the_sprint_contains_no_runtime_or_persistence_artifact(): void
    {
        $certification = file_get_contents(dirname(__DIR__, 2).'/docs/PHASE-4.7-WORKFLOW-CERTIFICATION.md');
        self::assertIsString($certification);
        self::assertStringContainsString('aucune persistance', $certification);
        self::assertStringContainsString('aucune migration', $certification);
        self::assertStringContainsString('aucun binding Runtime', $certification);
        self::assertStringContainsString('aucun événement', $certification);
    }
}
