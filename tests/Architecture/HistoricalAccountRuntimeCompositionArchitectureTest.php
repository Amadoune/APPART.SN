<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class HistoricalAccountRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_bindings_and_health_entries_are_unique(): void
    {
        $provider = $this->provider();

        self::assertSame(1, substr_count($provider, 'singleton(AccountPersistenceMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlAccountRepository::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAccountRepository::class, AccountRegistry::class)'));
        self::assertStringNotContainsString('RuntimeHealthComponent::HistoricalAccountRepository', $provider);
        self::assertStringNotContainsString('RuntimeHealthComponent::AccountRegistry', $provider);
    }

    public function test_composition_is_declarative_and_does_not_open_account_status_runtime(): void
    {
        $provider = $this->provider();
        $lines = array_filter(
            explode("\n", $provider),
            static fn (string $line): bool => str_contains($line, 'AccountRegistry')
                || str_contains($line, 'AccountRepository')
                || str_contains($line, 'AccountPersistenceMapper'),
        );
        $composition = implode("\n", $lines);

        foreach ([
            '->find(', '->add(', '->save(', 'beginTransaction(', 'commit(',
            'AccountStatusWorkflow', 'AccountStatusWorkflowStore',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $composition);
        }
    }

    public function test_runtime_health_catalog_remains_frozen_at_fifty_five(): void
    {
        $root = dirname(__DIR__, 2);
        $components = (string) file_get_contents($root.'/app/Application/RuntimeHealth/RuntimeHealthComponent.php');
        $requirements = (string) file_get_contents($root.'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');

        self::assertGreaterThanOrEqual(55, substr_count($components, 'case '));
        self::assertGreaterThanOrEqual(55, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertStringNotContainsString('HistoricalAccountRepository', $components);
        self::assertStringNotContainsString('AccountRegistry', $components);
    }

    public function test_certified_foundations_are_not_modified_by_composition(): void
    {
        $root = dirname(__DIR__, 2);
        foreach ([
            '/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/041_account_status_lifecycle_workflow.sql',
            '/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/042_historical_account_persistence.sql',
            '/src/Modules/IdentityAccess/Application/Contract/AccountRegistry.php',
        ] as $file) {
            $source = (string) file_get_contents($root.$file);
            self::assertStringNotContainsString('RuntimeHealth', $source);
            self::assertStringNotContainsString('ServiceProvider', $source);
        }
    }

    private function provider(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );
    }
}
