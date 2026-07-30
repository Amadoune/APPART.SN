<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecycleWorkflowArchitectureTest extends TestCase
{
    public function test_workflow_foundation_has_no_technical_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle';
        $files = array_filter(glob($root.'/*.php') ?: [], self::isWorkflowFoundationFile(...));
        self::assertCount(8, $files);
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', 'Http', 'Runtime', 'PDO', 'PostgreSql', 'Repository', 'Outbox', 'Worker', 'Consumer', 'Projection', 'Migration'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_workflow_has_no_clock_identity_event_or_external_input(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle';
        foreach (array_filter(glob($root.'/*.php') ?: [], self::isWorkflowFoundationFile(...)) as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['DateTime', 'Carbon', 'now(', 'time(', 'UUID', 'Uuid', 'random', 'Event', 'PropertyId', 'PropertyRegistry'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_decision_mapping_is_closed_without_default_branch(): void
    {
        $workflow = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/PropertyLifecycleWorkflow.php');
        self::assertStringNotContainsString('default', $workflow);
        self::assertSame(12, substr_count($workflow, "' => PropertyLifecycleState::"));
        self::assertSame(9, substr_count($workflow, 'PropertyLifecycleAction::'));
    }

    public function test_existing_property_domain_is_not_coupled_to_the_new_workflow(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Domain';
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                self::assertStringNotContainsString('PropertyLifecycle', (string) file_get_contents($file->getPathname()));
            }
        }
    }

    private static function isWorkflowFoundationFile(string $file): bool
    {
        return ! str_contains($file, 'Persistence')
            && ! str_contains($file, 'StoredState')
            && ! str_contains($file, 'Orchestration')
            && ! str_contains($file, 'DeterministicPropertyLifecycleOrchestrator');
    }
}
