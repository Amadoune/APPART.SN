<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusAtomicEventIntegrationArchitectureTest extends TestCase
{
    public function test_integration_uses_certified_orchestrator_router_and_generic_routed_writer_once(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/AccountStatusEventIntegration/AccountStatusAtomicEventOrchestrator.php',
        );

        self::assertSame(1, substr_count($source, '$this->orchestrator->transition('));
        self::assertSame(1, substr_count($source, '$this->router->route('));
        self::assertSame(1, substr_count($source, '$this->outbox->appendRouted('));
        self::assertSame(1, substr_count($source, 'PublicProjectionRoutedDeliveryMessageV1::fromDecision('));
        self::assertStringContainsString(
            'AccountStatusOrchestrationStatus::Applied',
            $source,
        );
        foreach ([
            'publish(', 'dispatch(', 'send(', 'Worker', 'Http', 'Controller',
            'consumeRouted(', 'new PDO', 'beginTransaction(', 'commit(', 'rollBack(',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_one_generic_transaction_owns_account_status_and_outbox_commit(): void
    {
        $root = dirname(__DIR__, 2);
        $transaction = (string) file_get_contents(
            $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlAggregateOutboxTransaction.php',
        );
        $accountTransaction = (string) file_get_contents(
            $root.'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/PostgreSqlAccountStatusOrchestrationTransaction.php',
        );

        self::assertSame(2, substr_count($transaction, 'AccountStatusAtomicTransaction'));
        self::assertSame(1, substr_count($transaction, 'beginTransaction()'));
        self::assertSame(1, substr_count($transaction, 'commit()'));
        self::assertSame(1, substr_count($transaction, 'rollBack()'));
        self::assertStringContainsString(
            '$owner = ! $this->connection->inTransaction();',
            $accountTransaction,
        );
    }

    public function test_runtime_composition_is_additive_and_contains_no_execution(): void
    {
        $provider = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );

        self::assertSame(1, substr_count($provider, 'singleton(AccountStatusAtomicEventOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAggregateOutboxTransaction::class, AccountStatusAtomicTransaction::class)'));
        self::assertSame(1, substr_count($provider, 'alias(DeterministicAccountStatusEventRouter::class, AccountStatusEventRouter::class)'));
        self::assertStringNotContainsString(
            'AccountStatusAtomicEventOrchestrator::transition(',
            $provider,
        );
    }
}
