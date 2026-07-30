<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleContextualPersistenceArchitectureTest extends TestCase
{
    public function test_contextual_persistence_has_no_forbidden_dependencies(): void
    {
        $root = dirname(__DIR__, 2);
        $files = [
            ...glob($root.'/src/Modules/Media/Application/MediaItemLifecycleContext/*.php') ?: [],
            ...glob($root.'/src/Modules/Media/Application/MediaItemLifecycleContext/Contract/*.php') ?: [],
            $root.'/src/Modules/Media/Infrastructure/Persistence/MediaItemLifecycleContextMapper.php',
            $root.'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaItemLifecycleContextualTransitionRepository.php',
            $root.'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaItemLifecycleContextualReplayInspector.php',
        ];

        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'Http', 'Outbox', 'Inbox', 'Consumer', 'Worker', 'EventRouter', 'Orchestrator', 'MediaCollectionRepository', 'now(', 'random_bytes'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_migration_032_is_additive_and_does_not_modify_031(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/';
        $up = (string) file_get_contents($root.'032_media_item_lifecycle_context.sql');
        $down = (string) file_get_contents($root.'032_media_item_lifecycle_context.down.sql');
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS media.media_item_lifecycle_transition_contexts', $up);
        self::assertStringNotContainsString('ALTER TABLE', $up);
        self::assertStringNotContainsString('media_item_lifecycle_transitions', $down);
        self::assertSame('DROP TABLE IF EXISTS media.media_item_lifecycle_transition_contexts;', trim($down));
    }

    public function test_repository_only_materializes_certified_transition_and_context(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaItemLifecycleContextualTransitionRepository.php';
        $contents = (string) file_get_contents($file);
        self::assertStringNotContainsString('MediaCollection', $contents);
        self::assertStringNotContainsString('replacementSelected', $contents);
        self::assertStringNotContainsString('notPrimary', $contents);
        self::assertStringContainsString('$this->contextMapper->map($append)', $contents);
    }
}
