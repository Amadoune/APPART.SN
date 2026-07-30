<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecyclePersistenceArchitectureTest extends TestCase
{
    public function test_persistence_contains_no_collection_decision_or_runtime_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Media';
        $files = [
            ...glob($root.'/Application/MediaItemLifecyclePersistence/**/*.php') ?: [],
            ...glob($root.'/Application/MediaItemLifecyclePersistence/*.php') ?: [],
            $root.'/Infrastructure/Persistence/MediaItemLifecycleWorkflowMapper.php',
            $root.'/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaItemLifecycleWorkflowRepository.php',
        ];
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['MediaCollection', 'replacement', 'primary', 'MediaOrder', 'MediaChecksum', 'PropertyLifecycle', 'Illuminate', 'App\\Application\\Runtime', 'Outbox', 'Worker', 'Consumer'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_repository_does_not_duplicate_the_workflow_matrix(): void
    {
        $repository = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaItemLifecycleWorkflowRepository.php');
        self::assertStringNotContainsString('active>remove', $repository);
        self::assertStringNotContainsString('active>archive', $repository);
        self::assertStringNotContainsString('new MediaItemLifecycleWorkflow', $repository);
    }

    public function test_migration_is_closed_to_the_two_certified_transitions(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/031_media_item_lifecycle_workflow.sql');
        self::assertSame(1, substr_count($migration, "('active','remove','removed')"));
        self::assertSame(1, substr_count($migration, "('active','archive','archived')"));
        foreach (['collection_id', 'property_id', 'primary', 'replacement', 'media_order', 'media_checksum', 'timestamp', 'created_at'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $migration);
        }
    }
}
