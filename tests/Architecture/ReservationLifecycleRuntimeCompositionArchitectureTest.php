<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_existing_runtime_root_contains_one_lazy_reservation_graph(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(ReservationLifecycleWorkflow::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(ReservationLifecycleWorkflowMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlReservationLifecycleWorkflowRepository::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlReservationLifecycleWorkflowRepository::class, ReservationLifecycleWorkflowStore::class)'));
        self::assertSame([], glob($root.'/app/Providers/*ReservationLifecycle*') ?: []);
    }

    public function test_bootstrap_executes_no_workflow_storage_transaction_or_sql(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        foreach (['->decide(', '->initialize(', '->append(', '->read(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
        self::assertDoesNotMatchRegularExpression('/\b(?:SELECT|INSERT|UPDATE|DELETE)\b/', $provider);
    }

    public function test_runtime_health_only_inspects_the_two_application_capabilities(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringContainsString('RuntimeHealthComponent::ReservationLifecycleWorkflow, ReservationLifecycleWorkflow::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ReservationLifecycleWorkflowStore, ReservationLifecycleWorkflowStore::class', $requirements);
        foreach (['PostgreSqlReservationLifecycleWorkflowRepository', 'ReservationLifecycleWorkflowMapper', '->decide(', '->read(', '->append('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $requirements);
        }
    }

    public function test_composition_introduces_no_http_outbox_worker_event_projection_or_fake(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $reservationLines = array_filter(explode("\n", $provider), static fn (string $line): bool => str_contains($line, 'ReservationLifecycleWorkflow') || str_contains($line, 'PostgreSqlReservationLifecycleWorkflowRepository'));
        $composition = implode("\n", $reservationLines);
        foreach (['Http', 'Outbox', 'Worker', 'Consumer', 'Event', 'Projection', 'Fake', 'Null', 'Fallback'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $composition);
        }
    }
}
