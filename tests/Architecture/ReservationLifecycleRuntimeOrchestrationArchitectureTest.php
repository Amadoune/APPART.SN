<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleRuntimeOrchestrationArchitectureTest extends TestCase
{
    public function test_orchestrator_only_reads_decides_and_appends_once(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycleOrchestration/DeterministicReservationLifecycleEventOrchestrator.php');
        self::assertSame(1, substr_count($contents, '$this->store->read('));
        self::assertSame(1, substr_count($contents, '$this->workflow->decide('));
        self::assertSame(1, substr_count($contents, '$this->store->append('));
        self::assertStringNotContainsString('TRANSITIONS', $contents);
        self::assertStringNotContainsString('new ReservationLifecycleTransition', $contents);
        self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents);
    }

    public function test_orchestration_has_only_application_dependencies(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycleOrchestration';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['Illuminate', 'Laravel', 'Http', 'PDO', 'PostgreSql', 'Infrastructure', 'Outbox', 'Worker', 'Consumer', 'Projection', 'EventCatalog', 'EventPayload'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_runtime_registers_one_lazy_alias_without_execution_or_health_extension(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(DeterministicReservationLifecycleEventOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'alias(DeterministicReservationLifecycleEventOrchestrator::class, ReservationLifecycleEventOrchestrator::class)'));
        foreach (['->transition(', '->decide(', '->read(', '->append('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }

        $requirements = (string) file_get_contents($root.'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringNotContainsString('ReservationLifecycleEventOrchestrator', $requirements);
    }

    public function test_certified_workflow_and_persistence_remain_orchestration_free(): void
    {
        foreach ([
            dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycle/ReservationLifecycleWorkflow.php',
            dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/PostgreSqlReservationLifecycleWorkflowRepository.php',
        ] as $file) {
            $contents = (string) file_get_contents($file);
            self::assertStringNotContainsString('ReservationLifecycleEventOrchestrator', $contents);
            self::assertStringNotContainsString('ReservationLifecycleOrchestrationResult', $contents);
        }
    }
}
