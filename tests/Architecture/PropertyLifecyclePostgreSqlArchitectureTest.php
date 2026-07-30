<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecyclePostgreSqlArchitectureTest extends TestCase
{
    public function test_repository_implements_only_the_store_and_contains_no_business_matrix(): void
    {
        $repository = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/PostgreSqlPropertyLifecycleWorkflowRepository.php');
        self::assertStringContainsString('implements PropertyLifecycleWorkflowStore', $repository);
        foreach (['new PropertyLifecycleWorkflow', '->decide(', 'TRANSITIONS', 'PropertyLifecycleDecision', 'PropertyLifecycleAction::', 'PropertyLifecycleState::', "'draft>'", "'active>'"] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $repository);
        }
    }

    public function test_persistence_has_no_runtime_http_event_outbox_or_projection_dependency(): void
    {
        $files = [
            dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PropertyLifecycleWorkflowMapper.php',
            dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/PostgreSqlPropertyLifecycleWorkflowRepository.php',
        ];
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'Laravel', 'Http', 'RuntimeHealth', 'Outbox', 'Worker', 'Consumer', 'Projection', 'Event'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_mapper_only_maps_values_and_computes_the_deterministic_checksum(): void
    {
        $mapper = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PropertyLifecycleWorkflowMapper.php');
        self::assertStringContainsString("hash('sha256'", $mapper);
        self::assertStringNotContainsString('new PropertyLifecycleWorkflow', $mapper);
        self::assertStringNotContainsString('PropertyLifecycleAction::', $mapper);
        self::assertStringNotContainsString('now(', $mapper);
        self::assertStringNotContainsString('random', $mapper);
    }

    public function test_postgresql_owns_the_twelve_transition_integrity_constraint(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/017_property_lifecycle_workflow.sql');
        self::assertStringContainsString('property_lifecycle_allowed_transition', $migration);
        self::assertStringContainsString('CHECK (version > 0)', $migration);
        self::assertSame(12, substr_count($migration, "            ('"));
        self::assertStringNotContainsString('timestamp', strtolower($migration));
    }

    public function test_certified_workflow_and_property_domain_do_not_depend_on_persistence(): void
    {
        foreach ([
            dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/PropertyLifecycleWorkflow.php',
            dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Domain/Model/Property.php',
        ] as $file) {
            $contents = (string) file_get_contents($file);
            self::assertStringNotContainsString('PropertyLifecycleWorkflowStore', $contents);
            self::assertStringNotContainsString('PostgreSqlPropertyLifecycleWorkflowRepository', $contents);
        }
    }
}
