<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAccessRuntimeOrchestrationArchitectureTest extends TestCase
{
    #[Test]
    public function orchestration_contract_is_framework_and_delivery_independent(): void
    {
        $root = dirname(__DIR__, 2);
        $directory = $root.'/src/Modules/IdentityAccess/Application/IdentityAccessOrchestration';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach (['Illuminate\\', '\\Infrastructure\\', 'PDO', 'Outbox', 'Event', 'Controller', 'Route'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    #[Test]
    public function transaction_is_bound_without_http_event_or_outbox(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/IdentityAccessOrchestrationServiceProvider.php');
        $transaction = (string) file_get_contents(
            $root.'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/PostgreSqlIdentityAccessAtomicTransaction.php',
        );

        self::assertStringContainsString('IdentityAccessAtomicTransaction::class', $provider);
        self::assertStringContainsString('IdentityAccessOrchestrator::class', $provider);
        foreach (['Outbox', 'EventRouter', 'Controller', 'Middleware', 'Route::'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider.$transaction);
        }
    }

    #[Test]
    public function migration_053_is_additive_and_owner_scoped(): void
    {
        $migration = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/053_identity_access_atomic_operations.sql',
        );

        self::assertStringContainsString('identity_access_completion.atomic_operation_intents', $migration);
        self::assertStringNotContainsString('ALTER TABLE', strtoupper($migration));
        self::assertStringNotContainsString('REFERENCES ', strtoupper($migration));
        self::assertStringNotContainsString('OUTBOX', strtoupper($migration));
    }
}
