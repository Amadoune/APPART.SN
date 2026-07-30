<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleContextArchitectureTest extends TestCase
{
    public function test_foundation_is_purely_contractual(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Media/Application/MediaItemLifecycleContext';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(16, $files);

        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Infrastructure', 'PDO', 'PostgreSql', 'Repository', 'Store', 'Illuminate', 'Runtime', 'Orchestrator', 'Http', 'Event', 'Inbox', 'Outbox', 'Worker', 'Consumer', 'now(', 'time(', 'random', 'MediaCollectionRegistry'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_decision_has_no_implicit_or_ambiguous_factory(): void
    {
        $decision = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Media/Application/MediaItemLifecycleContext/MediaCollectionTransitionDecision.php');
        self::assertStringContainsString('private function __construct', $decision);
        self::assertSame(1, substr_count($decision, 'function notPrimary('));
        self::assertSame(1, substr_count($decision, 'function replacementSelected('));
        foreach (['infer', 'selectReplacement', 'isPrimary', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $decision);
        }
    }

    public function test_no_certified_foundation_imports_the_new_context(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['MediaItemLifecycle', 'MediaItemLifecyclePersistence'] as $slice) {
            foreach (glob($root.'/src/Modules/Media/Application/'.$slice.'/*.php') ?: [] as $file) {
                self::assertStringNotContainsString('MediaItemLifecycleContext', (string) file_get_contents($file));
            }
        }
    }
}
