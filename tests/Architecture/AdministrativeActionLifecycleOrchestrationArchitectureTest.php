<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleOrchestrationArchitectureTest extends TestCase
{
    public function test_replay_path_uses_only_inspection_and_policy_without_workflow_recall(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/AdministrativeActionLifecycleOrchestration/DeterministicAdministrativeActionLifecycleOrchestrator.php');
        $replay = substr($source, (int) strpos($source, 'private function replay'));
        self::assertStringContainsString('$this->inspector->inspectLatest', $replay);
        self::assertStringContainsString('$this->replayPolicy->classify', $replay);
        foreach (['$this->workflow', 'new AdministrativeActionLifecycleTransition', 'new AdministrativeActionDecisionContext'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $replay);
        }
    }

    public function test_orchestrator_has_no_event_outbox_http_or_worker_dependency(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/AdministrativeActionLifecycleOrchestration/DeterministicAdministrativeActionLifecycleOrchestrator.php');
        foreach (['PDO', 'Event', 'Inbox', 'Outbox', 'Worker', 'Http', 'now(', 'random_'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_runtime_exposes_only_the_orchestrator_as_new_health_capability(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringContainsString('RuntimeHealthComponent::AdministrativeActionLifecycleOrchestrator, AdministrativeActionLifecycleOrchestrator::class', $requirements);
        self::assertStringNotContainsString('AdministrativeActionContextualReplayInspector::class', $requirements);
        self::assertStringNotContainsString('AdministrativeActionContextualTransitionStore::class', $requirements);
    }
}
