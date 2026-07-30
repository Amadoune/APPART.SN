<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleAtomicEventIntegrationArchitectureTest extends TestCase
{
    public function test_integrator_uses_exact_inspection_without_business_reconstruction(): void
    {
        $contents = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/AdministrativeActionLifecycleEventIntegration/AdministrativeActionLifecycleAtomicEventOrchestrator.php',
        );

        self::assertSame(1, substr_count($contents, '->inspectLatest('));
        self::assertSame(1, substr_count($contents, '->append('));
        self::assertStringContainsString('$inspection->snapshot->transition', $contents);
        self::assertStringContainsString('$inspection->snapshot->context->actor', $contents);
        foreach ([
            'new AdministrativeActionLifecycleTransition',
            'AdministrativeActionLifecycleWorkflow',
            'FourEyesPolicy',
            'PDO',
            'beginTransaction',
            'Http',
            'Worker',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_generic_transaction_and_outbox_foundations_are_reused_once(): void
    {
        $provider = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );

        self::assertSame(1, substr_count(
            $provider,
            'alias(PostgreSqlAggregateOutboxTransaction::class, AdministrativeActionLifecycleAtomicTransaction::class)',
        ));
        self::assertSame(1, substr_count(
            $provider,
            'singleton(AdministrativeActionLifecycleAtomicEventOrchestrator::class)',
        ));
        self::assertStringNotContainsString('AdministrativeActionLifecycleOutboxWriter', $provider);
    }

    public function test_frozen_migrations_and_contracts_remain_free_of_integration(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/Migrations/036_administrative_action_lifecycle_event_inbox.sql',
            'app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/037_administration_audit_outbox_owner.sql',
            'app/Application/AdministrativeActionLifecycleEventTransport/AdministrativeActionLifecycleDeliveryPayload.php',
        ] as $file) {
            self::assertStringNotContainsString(
                'AdministrativeActionLifecycleAtomicEventOrchestrator',
                (string) file_get_contents($root.'/'.$file),
            );
        }
    }
}
