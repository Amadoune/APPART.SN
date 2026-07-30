<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusHistoricalAccountCompatibilityArchitectureTest extends TestCase
{
    public function test_the_two_persistence_foundations_share_the_same_ports_without_crossing_owners(): void
    {
        $root = dirname(__DIR__, 2);
        $statusStore = (string) file_get_contents(
            $root.'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/PostgreSqlAccountStatusWorkflowStore.php',
        );
        $accountRepository = (string) file_get_contents(
            $root.'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/PostgreSqlAccountRepository.php',
        );

        self::assertStringContainsString('private PDO $connection', $statusStore);
        self::assertStringContainsString('private AccountRegistry $accounts', $statusStore);
        self::assertStringContainsString('private PDO $connection', $accountRepository);
        self::assertStringContainsString('implements AccountRegistry', $accountRepository);

        foreach (['Account::suspend(', 'Account::reactivate(', '->save('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $statusStore);
        }
        self::assertStringNotContainsString('account_status_lifecycle_transitions', $accountRepository);
    }

    public function test_certified_runtime_candidate_reuses_the_single_pdo(): void
    {
        $provider = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );

        self::assertSame(1, substr_count($provider, 'singleton(PDO::class'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlAccountRepository::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAccountRepository::class, AccountRegistry::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlAccountStatusWorkflowStore::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAccountStatusWorkflowStore::class, AccountStatusWorkflowStore::class)'));
    }
}
