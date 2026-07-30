<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusAtomicEventIntegrationArchitectureTest extends TestCase
{
    public function test_integrator_uses_exact_inspection_and_contains_no_business_reconstruction(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/ProfessionalStatusEventIntegration/ProfessionalStatusAtomicEventOrchestrator.php');
        self::assertSame(1, substr_count($contents, '->inspectLatest('));
        self::assertSame(1, substr_count($contents, '->append('));
        self::assertStringContainsString('$inspection->snapshot->transition', $contents);
        foreach (['new ProfessionalStatusTransition', 'ProfessionalStatusWorkflow', 'PDO', 'beginTransaction', 'Http', 'Worker'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_existing_transaction_and_outbox_foundations_are_reused_once(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAggregateOutboxTransaction::class, ProfessionalStatusAtomicTransaction::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(ProfessionalStatusAtomicEventOrchestrator::class)'));
        self::assertStringNotContainsString('ProfessionalStatusOutboxWriter', $provider);
    }

    public function test_frozen_migrations_and_compatibility_remain_unchanged_by_integration(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['app/Infrastructure/ProfessionalStatusEventRouting/PostgreSql/Migrations/029_professional_status_event_inbox.sql', 'app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/030_professionals_outbox_owner.sql'] as $file) {
            self::assertStringNotContainsString('ProfessionalStatusAtomicEventOrchestrator', (string) file_get_contents($root.'/'.$file));
        }
    }
}
