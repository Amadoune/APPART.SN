<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrationConsoleOutboxFoundationArchitectureTest extends TestCase
{
    public function test_application_outbox_has_deliveries_as_its_only_sources(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Application/Outbox';
        $files = glob($root.'/*.php') ?: [];
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        self::assertCount(5, $files);
        foreach (['AdministrationOperatorDeliveryV1', 'AdministrationQueueDeliveryV1', 'AdministrationAuditDeliveryV1'] as $delivery) {
            self::assertStringContainsString($delivery, $php);
        }
        foreach (['Application\\Event', 'PublicRead', 'OwnerReader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'PDO', 'SQL', 'Infrastructure\\', 'Provider', 'Transport', 'Routing', 'Consumer'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_repository_is_the_only_postgresql_enclave_and_migration_082_is_frozen(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/AdministrationConsole/Infrastructure/Outbox/PostgreSqlAdministrationConsoleOutboxRepository.php');
        foreach (['SAVEPOINT', 'FOR UPDATE', 'ON CONFLICT', 'ORDER BY created_at,message_id', 'MAX_RETRIES'] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        self::assertSame(1, substr_count($repository, 'final readonly class PostgreSqlAdministrationConsoleOutboxRepository'));
        self::assertSame('ed6987ae4605d723d25b7e58b5148535e650d1bec397302dbb202da6abc3e544', hash_file('sha256', $root.'/src/Modules/AdministrationConsole/Infrastructure/Persistence/PostgreSql/Migrations/082_administration_console_owner_source.sql'));
        self::assertSame('83f175e530ca739469e86d7c47a9773e0082d7c20f1424a36dfafb4493208fea', hash_file('sha256', $root.'/src/Modules/AdministrationConsole/Infrastructure/Persistence/PostgreSql/Migrations/082_administration_console_owner_source.down.sql'));
    }
}
