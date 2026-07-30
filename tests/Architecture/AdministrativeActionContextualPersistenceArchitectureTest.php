<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionContextualPersistenceArchitectureTest extends TestCase
{
    public function test_contextual_persistence_is_additive_and_isolated(): void
    {
        $root = dirname(__DIR__, 2);
        $files = [
            $root.'/src/Modules/AdministrationAudit/Application/AdministrativeActionTransitionContext/Contract/AdministrativeActionContextualTransitionStore.php',
            $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/AdministrativeActionTransitionContextMapper.php',
            $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionContextualTransitionRepository.php',
            $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionContextualReplayInspector.php',
        ];
        foreach ($files as $file) {
            $source = (string) file_get_contents($file);
            foreach (['new AdministrativeActionLifecycleWorkflow', '->decide(', 'Outbox', 'Inbox', 'Http', 'Event', 'Worker'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, basename($file));
            }
        }
    }

    public function test_migration_035_is_the_only_schema_change_and_034_is_not_referenced_as_mutable(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/';
        $up = (string) file_get_contents($root.'035_administrative_action_lifecycle_context.sql');
        $down = (string) file_get_contents($root.'035_administrative_action_lifecycle_context.down.sql');
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS administration_audit.administrative_action_lifecycle_transition_contexts', $up);
        self::assertStringContainsString('DROP TABLE IF EXISTS administration_audit.administrative_action_lifecycle_transition_contexts', $down);
        self::assertStringNotContainsString('ALTER TABLE', $up);
        self::assertStringNotContainsString('administrative_action_lifecycle_transitions', $down);
    }

    public function test_runtime_orchestration_composes_the_certified_contextual_components_without_adapter(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(2, substr_count($provider, 'PostgreSqlAdministrativeActionContextualTransitionRepository::class'));
        self::assertSame(2, substr_count($provider, 'PostgreSqlAdministrativeActionContextualReplayInspector::class'));
        self::assertSame([], glob($root.'/app/Http/**/*AdministrativeActionContextual*') ?: []);
    }
}
