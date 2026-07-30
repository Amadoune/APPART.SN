<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class HistoricalAccountPostgreSqlPersistenceArchitectureTest extends TestCase
{
    public function test_only_the_certified_postgresql_components_are_added(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/';

        self::assertFileExists($root.'PostgreSql/PostgreSqlAccountRepository.php');
        self::assertFileExists($root.'PostgreSql/Migrations/042_historical_account_persistence.sql');
        self::assertFileExists($root.'PostgreSql/Migrations/042_historical_account_persistence.down.sql');
        self::assertFileExists($root.'HistoricalAccount/AccountPersistenceMapper.php');
    }

    public function test_repository_implements_the_unchanged_registry_and_uses_the_certified_mapper(): void
    {
        $source = $this->repository();

        self::assertStringContainsString('implements AccountRegistry', $source);
        self::assertStringContainsString('AccountPersistenceMapper $mapper', $source);
        self::assertSame(1, substr_count($source, 'function find('));
        self::assertSame(1, substr_count($source, 'function add('));
        self::assertSame(1, substr_count($source, 'function save('));
    }

    public function test_optimistic_concurrency_is_a_single_conditional_update(): void
    {
        $source = $this->repository();

        self::assertStringContainsString('AND historical_version=:expected_version', $source);
        self::assertStringContainsString('$statement->rowCount() !== 1', $source);
        self::assertStringContainsString('throw new ConcurrentAccountModification', $source);
    }

    public function test_repository_has_no_runtime_event_outbox_http_or_lifecycle_mutation(): void
    {
        $source = $this->repository();

        foreach ([
            'ServiceProvider', 'RuntimeHealth', 'Illuminate\\', 'Event', 'Outbox',
            'Http', '->releaseEvents(', '->suspend(', '->reactivate(',
            'AccountStatusWorkflow', 'account_status_lifecycle_transitions',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source, $forbidden);
        }
    }

    public function test_migration_is_additive_reversible_and_does_not_touch_041(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/';
        $up = (string) file_get_contents($root.'042_historical_account_persistence.sql');
        $down = (string) file_get_contents($root.'042_historical_account_persistence.down.sql');

        foreach (['accounts', 'account_credentials', 'account_verifications', 'account_role_assignments', 'account_consents'] as $table) {
            self::assertStringContainsString('identity_access.'.$table, $up);
            self::assertStringContainsString('identity_access.'.$table, $down);
        }
        self::assertStringNotContainsString('account_status_lifecycle_transitions', $up.$down);
        self::assertStringNotContainsString('ALTER TABLE', $up);
        self::assertStringNotContainsString('038_', $up.$down);
        self::assertStringNotContainsString('039_', $up.$down);
        self::assertStringNotContainsString('040_', $up.$down);
        self::assertStringNotContainsString('041_', $up.$down);
    }

    private function repository(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/PostgreSqlAccountRepository.php',
        );
    }
}
