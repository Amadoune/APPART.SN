<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class HistoricalAccountSecureSnapshotArchitectureTest extends TestCase
{
    public function test_snapshot_boundary_has_no_infrastructure_or_runtime_dependency(): void
    {
        $source = $this->productionSource();

        foreach ([
            'PDO', 'Illuminate\\', 'RuntimeHealth', 'ServiceProvider', 'DB::',
            'Http\\', 'EventTransport', 'Outbox', 'json_encode(', 'json_decode(',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source, $forbidden);
        }
    }

    public function test_mapper_contains_no_sql_reflection_serialization_or_business_mutation(): void
    {
        $mapper = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/HistoricalAccount/AccountPersistenceMapper.php',
        );

        foreach ([
            'PDO', 'SELECT ', 'INSERT ', 'UPDATE ', 'DELETE ',
            'ReflectionClass', 'ReflectionProperty', 'serialize(', 'unserialize(',
            '->verify(', '->grantRole(', '->revokeRole(', '->grantConsent(',
            '->withdrawConsent(', '->suspend(', '->reactivate(',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $mapper, $forbidden);
        }
    }

    public function test_boundary_uses_no_reflection_or_visibility_bypass(): void
    {
        $source = $this->productionSource();

        foreach ([
            'ReflectionClass', 'ReflectionProperty', 'setAccessible(',
            'Closure::bind', 'bindTo(', 'unserialize(',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source, $forbidden);
        }
        self::assertStringNotContainsString('function __toString', $source);
    }

    public function test_no_setter_is_added_to_the_account_aggregate_graph(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Domain/Model/';
        foreach (['Account.php', 'Credential.php', 'Verification.php', 'RoleAssignment.php', 'Consent.php'] as $file) {
            $source = (string) file_get_contents($root.$file);
            self::assertDoesNotMatchRegularExpression('/public function set[A-Z]/', $source, $file);
        }
    }

    public function test_account_registry_contract_is_unchanged(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Application/Contract/AccountRegistry.php',
        );

        self::assertSame(1, substr_count($source, 'function find('));
        self::assertSame(1, substr_count($source, 'function add('));
        self::assertSame(1, substr_count($source, 'function save('));
        self::assertStringNotContainsString('Snapshot', $source);
    }

    public function test_secret_reveal_and_snapshots_remain_inside_the_authorized_slice(): void
    {
        $root = dirname(__DIR__, 2).'/src';
        $authorizedPaths = [
            '/Modules/IdentityAccess/Infrastructure/Persistence/HistoricalAccount/',
            '/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/PostgreSqlAccountRepository.php',
        ];
        $snapshotNames = [
            'HistoricalAccountPersistenceSnapshotV1',
            'CredentialPersistenceSnapshotV1',
            'VerificationPersistenceSnapshotV1',
            'RoleAssignmentPersistenceSnapshotV1',
            'ConsentPersistenceSnapshotV1',
        ];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $path = str_replace('\\', '/', $file->getPathname());
            $source = (string) file_get_contents($file->getPathname());
            if (str_contains($source, '->revealForPersistence()')) {
                self::assertTrue($this->isAuthorizedPath($path, $authorizedPaths));
            }
            foreach ($snapshotNames as $snapshot) {
                if (str_contains($source, $snapshot)) {
                    self::assertTrue($this->isAuthorizedPath($path, $authorizedPaths), $snapshot.' leaked through '.$path);
                }
            }
        }
    }

    /** @param list<string> $authorizedPaths */
    private function isAuthorizedPath(string $path, array $authorizedPaths): bool
    {
        foreach ($authorizedPaths as $authorizedPath) {
            if (str_contains($path, $authorizedPath)) {
                return true;
            }
        }

        return false;
    }

    private function productionSource(): string
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess';
        $files = array_merge(
            glob($root.'/Domain/Persistence/*.php') ?: [],
            glob($root.'/Infrastructure/Persistence/HistoricalAccount/*.php') ?: [],
        );

        return implode("\n", array_map(
            static fn (string $file): string => (string) file_get_contents($file),
            $files,
        ));
    }
}
