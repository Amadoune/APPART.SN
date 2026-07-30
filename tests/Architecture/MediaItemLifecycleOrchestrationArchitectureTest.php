<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleOrchestrationArchitectureTest extends TestCase
{
    public function test_orchestration_has_no_forbidden_dependency_or_collection_reconstruction(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Media/Application/MediaItemLifecycleOrchestration';
        foreach ($this->phpFiles($root) as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['PDO', 'PostgreSql', 'Illuminate', 'Http', 'Outbox', 'Inbox', 'Consumer', 'Worker', 'Event', 'MediaCollectionRegistry', 'MediaCollectionRepository', 'replacementSelected', 'notPrimary', 'now(', 'random'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_replay_path_uses_inspection_and_policy_without_workflow(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/Media/Application/MediaItemLifecycleOrchestration/DeterministicMediaItemLifecycleOrchestrator.php';
        $contents = (string) file_get_contents($file);
        $replay = substr($contents, (int) strpos($contents, 'private function replay'));
        self::assertStringContainsString('$this->inspector->inspectLatest', $replay);
        self::assertStringContainsString('$this->replayPolicy->classify', $replay);
        self::assertStringNotContainsString('$this->workflow', $replay);
        self::assertStringNotContainsString('new MediaItemLifecycleTransition', $replay);
    }

    public function test_runtime_composes_one_orchestrator_without_parallel_provider(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(DeterministicMediaItemLifecycleOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'alias(DeterministicMediaItemLifecycleOrchestrator::class, MediaItemLifecycleOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'RuntimeHealthComponent::MediaItemLifecycleOrchestrator, MediaItemLifecycleOrchestrator::class'));
    }

    /** @return list<string> */
    private function phpFiles(string $root): array
    {
        $files = glob($root.'/*.php') ?: [];
        $contracts = glob($root.'/Contract/*.php') ?: [];

        return [...$files, ...$contracts];
    }
}
