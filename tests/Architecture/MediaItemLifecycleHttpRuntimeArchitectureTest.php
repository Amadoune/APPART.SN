<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleHttpRuntimeArchitectureTest extends TestCase
{
    public function test_the_controller_is_only_an_atomic_http_adapter(): void
    {
        $source = $this->source('app/Http/Controllers/MediaItemLifecycleHttpController.php');

        self::assertStringContainsString('MediaItemLifecycleAtomicEventOrchestrator', $source);
        self::assertSame(1, substr_count($source, '->transition('));
        foreach (['MediaItemLifecycleWorkflow', 'MediaItemLifecycleWorkflowStore', 'MediaItemLifecycleContextualTransitionStore', 'PublicProjectionOutbox', 'PDO', 'MediaCollection'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_the_request_only_transports_the_explicit_collection_decision(): void
    {
        $source = $this->source('app/Http/Requests/MediaItemLifecycleTransitionRequest.php');

        self::assertStringContainsString('MediaItemLifecycleAction::Remove', $source);
        self::assertStringContainsString('MediaItemLifecycleAction::Archive', $source);
        self::assertStringContainsString('MediaCollectionTransitionDecision::notPrimary()', $source);
        self::assertStringContainsString('MediaCollectionTransitionDecision::replacementSelected(', $source);
        self::assertStringNotContainsString('MediaItemLifecycleAction::Unknown->value', $source);
        foreach (['MediaItemLifecycleWorkflow', 'MediaCollectionRepository', 'now(', 'random_', 'PublicProjectionOutbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_the_mapper_exhaustively_names_all_eight_results_without_default(): void
    {
        $source = $this->source('app/Http/MediaItemLifecycleHttpResultMapper.php');

        foreach (['Applied', 'AlreadyApplied', 'Missing', 'VersionConflict', 'Denied', 'StateConflict', 'ContextDivergence', 'PersistenceCorrupted'] as $status) {
            self::assertSame(1, substr_count($source, "MediaItemLifecycleOrchestrationStatus::$status"));
        }
        self::assertStringNotContainsString('default', $source);
    }

    private function source(string $path): string
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/'.$path);
        self::assertIsString($source);

        return $source;
    }
}
