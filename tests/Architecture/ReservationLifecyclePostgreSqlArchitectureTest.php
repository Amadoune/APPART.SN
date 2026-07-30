<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecyclePostgreSqlArchitectureTest extends TestCase
{
    public function test_repository_implements_only_the_store_and_contains_no_business_matrix(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/PostgreSqlReservationLifecycleWorkflowRepository.php');
        self::assertStringContainsString('implements ReservationLifecycleWorkflowStore', $contents);
        foreach (['new ReservationLifecycleWorkflow', '->decide(', 'TRANSITIONS', 'ReservationLifecycleDecision', 'ReservationLifecycleAction::', 'ReservationLifecycleState::', "'draft>'", "'requested>'"] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_persistence_has_no_runtime_http_event_outbox_or_projection_dependency(): void
    {
        foreach ([
            dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/ReservationLifecycleWorkflowMapper.php',
            dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/PostgreSqlReservationLifecycleWorkflowRepository.php',
        ] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'Laravel', 'Http', 'RuntimeHealth', 'Outbox', 'Worker', 'Consumer', 'Projection', 'Event'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_mapper_only_maps_values_and_computes_the_deterministic_checksum(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/ReservationLifecycleWorkflowMapper.php');
        self::assertStringContainsString("hash('sha256'", $contents);
        self::assertStringNotContainsString('new ReservationLifecycleWorkflow', $contents);
        self::assertStringNotContainsString('ReservationLifecycleAction::', $contents);
        self::assertStringNotContainsString('now(', $contents);
        self::assertStringNotContainsString('random', $contents);
    }

    public function test_postgresql_owns_exactly_the_eleven_transition_constraints(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/019_reservation_lifecycle_workflow.sql');
        self::assertStringContainsString('reservation_lifecycle_allowed_transition', $contents);
        self::assertStringContainsString('CHECK (version > 0)', $contents);
        self::assertSame(11, substr_count($contents, "            ('"));
        self::assertStringNotContainsString('timestamp', strtolower($contents));
    }

    public function test_certified_workflow_does_not_depend_on_persistence(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycle/ReservationLifecycleWorkflow.php');
        self::assertStringNotContainsString('ReservationLifecycleWorkflowStore', $contents);
        self::assertStringNotContainsString('PostgreSqlReservationLifecycleWorkflowRepository', $contents);
        self::assertStringNotContainsString('ReservationId', $contents);
    }
}
