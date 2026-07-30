<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecyclePersistenceArchitectureTest extends TestCase
{
    public function test_the_persistence_layer_has_no_runtime_event_or_http_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $files = [
            $root.'/src/Modules/AdministrationAudit/Application/AdministrativeActionLifecyclePersistence/Contract/AdministrativeActionLifecycleWorkflowStore.php',
            $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/AdministrativeActionLifecycleWorkflowMapper.php',
            $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionLifecycleRepository.php',
            $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction.php',
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertStringNotContainsString('Illuminate\\', $contents);
            self::assertStringNotContainsString('Outbox', $contents);
            self::assertStringNotContainsString('EventRouter', $contents);
            self::assertStringNotContainsString('Http', $contents);
            self::assertStringNotContainsString('RuntimeHealth', $contents);
        }
    }

    public function test_the_repository_does_not_call_the_workflow_or_historical_repository(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionLifecycleRepository.php');
        self::assertIsString($contents);
        self::assertStringNotContainsString('new AdministrativeActionLifecycleWorkflow', $contents);
        self::assertStringNotContainsString('->decide(', $contents);
        self::assertStringNotContainsString('PostgreSqlAdministrativeActionRepository', $contents);
        self::assertStringNotContainsString('AdministrativeActionMapper', $contents);
    }

    public function test_historical_persistence_is_still_frozen(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertSame(
            'ac274e54b8b0c63f946c3032cd6fc9d72c1536e6e108df51cb099a3e1c4969c3',
            hash_file('sha256', $root.'/src/Modules/AdministrationAudit/Application/Contract/AdministrativeActionRegistry.php'),
        );
        self::assertSame(
            'c303c7595cdce36ed1fa255d8a76d4cdc5be4eeafe54b7a02b3fda58e2f0803f',
            hash_file('sha256', $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionRepository.php'),
        );
        self::assertSame(
            '8aa302c830763be09f9906fd51a6bb38c14604d8328b91dd210eebc68b00100e',
            hash_file('sha256', $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/AdministrativeActionMapper.php'),
        );
        self::assertSame(
            '0f099872948cf3a36b800f99176740efa2dd5c8e2276a9483da02b0dbfeb7885',
            hash_file('sha256', $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/001_administrative_action.sql'),
        );
    }

    public function test_migration_is_strictly_additive_and_scoped(): void
    {
        $migration = file_get_contents(dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/034_administrative_action_lifecycle_workflow.sql');
        self::assertIsString($migration);
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS administration_audit.administrative_action_lifecycle_transitions', $migration);
        self::assertStringNotContainsString('ALTER TABLE', $migration);
        self::assertStringNotContainsString('administrative_actions ADD', $migration);
    }
}
