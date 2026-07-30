<?php

namespace Tests\Architecture;

use App\Application\PlaceLifecycleEventIntegration\Contract\PlaceLifecycleAtomicTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use PHPUnit\Framework\TestCase;

final class PlaceLifecycleAtomicEventIntegrationArchitectureTest extends TestCase
{
    public function test_atomic_integration_reuses_the_single_generic_transaction_and_outbox_writer(): void
    {
        self::assertTrue(is_a(
            PostgreSqlAggregateOutboxTransaction::class,
            PlaceLifecycleAtomicTransaction::class,
            true,
        ));

        $orchestrator = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/PlaceLifecycleEventIntegration/PlaceLifecycleAtomicEventOrchestrator.php',
        );
        self::assertSame(1, substr_count($orchestrator, '->run('));
        self::assertSame(1, substr_count($orchestrator, '->execute('));
        self::assertSame(1, substr_count($orchestrator, '->append('));
        self::assertStringNotContainsString('PDO', $orchestrator);
        self::assertStringNotContainsString('beginTransaction', $orchestrator);
    }

    public function test_runtime_composition_is_unique_lazy_and_additive(): void
    {
        $provider = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );

        self::assertSame(1, substr_count($provider, 'PlaceLifecycleAtomicTransaction::class'));
        self::assertSame(1, substr_count($provider, 'singleton(PlaceLifecycleAtomicEventOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlPlaceMergeContextInspector::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(DeterministicPlaceMergeReplayClassifier::class)'));
    }

    public function test_no_specialized_outbox_or_migration_change_is_introduced(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['Writer', 'Reader', 'Mapper', 'Worker'] as $suffix) {
            self::assertFileDoesNotExist(
                $root.'/app/Application/PlaceLifecycleEventIntegration/PlaceLifecycle'.$suffix.'.php',
            );
        }
        self::assertStringNotContainsString(
            'public_projection_outbox',
            (string) file_get_contents(
                $root.'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/038_place_lifecycle_workflow.sql',
            ),
        );
        self::assertStringNotContainsString(
            'public_projection_outbox',
            (string) file_get_contents(
                $root.'/app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/Migrations/039_place_lifecycle_event_inbox.sql',
            ),
        );
        self::assertStringNotContainsString(
            'place_lifecycle_transitions',
            (string) file_get_contents(
                $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/040_geography_outbox_owner.sql',
            ),
        );
    }
}
