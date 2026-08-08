<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class NotificationsOutboxFoundationArchitectureTest extends TestCase
{
    public function test_application_outbox_has_delivery_as_its_only_source(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Notifications/Application/Outbox';
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), glob($root.'/*.php') ?: []));
        self::assertStringContainsString('NotificationDeliveryV1', $php);
        foreach (['NotificationEvent', 'PublicRead', 'OwnerReader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'PDO', 'SQL', 'Infrastructure\\', 'Provider', 'Transport', 'Routing', 'Consumer'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_repository_is_the_only_postgresql_enclave_and_migration_079_is_frozen(): void
    {
        $repository = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Notifications/Infrastructure/Outbox/PostgreSqlNotificationsOutboxRepository.php');
        self::assertStringContainsString('SAVEPOINT', $repository);
        self::assertStringContainsString('NotificationDeliveryV1', $repository);
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Notifications/Infrastructure/Persistence/PostgreSql/Migrations/079_notifications_owner_source.sql');
        self::assertSame('86ebe9e9c191f81822526321ecc960334dcb5869d84882bcec145de5b4f711ad', hash('sha256', $migration));
    }
}
