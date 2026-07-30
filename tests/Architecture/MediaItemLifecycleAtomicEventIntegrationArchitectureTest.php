<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleAtomicEventIntegrationArchitectureTest extends TestCase
{
    public function test_integrator_uses_exact_inspection_and_contains_no_business_reconstruction(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/MediaItemLifecycleEventIntegration/MediaItemLifecycleAtomicEventOrchestrator.php');

        self::assertSame(1, substr_count($contents, '->inspectLatest('));
        self::assertSame(1, substr_count($contents, '->append('));
        self::assertStringContainsString('$inspection->snapshot->transition', $contents);
        foreach (['new MediaItemLifecycleTransition', 'MediaItemLifecycleWorkflow', 'MediaCollectionTransitionDecision', 'PDO', 'beginTransaction', 'Http', 'Worker'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_existing_transaction_and_outbox_foundations_are_reused_once(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAggregateOutboxTransaction::class, MediaItemLifecycleAtomicTransaction::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(MediaItemLifecycleAtomicEventOrchestrator::class)'));
        self::assertStringNotContainsString('MediaItemLifecycleOutboxWriter', $provider);
    }

    public function test_frozen_migrations_and_contracts_remain_free_of_integration(): void
    {
        $root = dirname(__DIR__, 2);

        foreach (['app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/Migrations/033_media_item_lifecycle_event_inbox.sql', 'app/Application/MediaItemLifecycleEventTransport/MediaItemLifecycleDeliveryPayload.php'] as $file) {
            self::assertStringNotContainsString('MediaItemLifecycleAtomicEventOrchestrator', (string) file_get_contents($root.'/'.$file));
        }
    }
}
