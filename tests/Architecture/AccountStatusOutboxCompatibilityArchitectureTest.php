<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusOutboxCompatibilityArchitectureTest extends TestCase
{
    public function test_catalog_mapper_schema_and_registry_use_only_generic_extension_points(): void
    {
        $root = dirname(__DIR__, 2);
        $catalog = (string) file_get_contents($root.'/app/Application/PublicProjectionDelivery/PublicProjectionDeliveryEventCatalog.php');
        $mapper = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxMapper.php');
        $schema = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxSchema.php');
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $worker = (string) file_get_contents($root.'/app/Application/PublicProjectionWorker/PublicProjectionDeliveryWorker.php');

        self::assertSame(1, substr_count($catalog, 'foreach (AccountStatusEventType::cases()'));
        self::assertSame(1, substr_count($mapper, 'AccountStatusDeliveryPayload::restore($data)'));
        self::assertSame(1, substr_count($schema, "'IdentityAccess' => 'identity_access'"));
        self::assertSame(1, substr_count($schema, "'identity_access' => 'IdentityAccess'"));
        self::assertSame(1, substr_count($provider, 'PublicProjectionDeliveryMode::RoutedV1'));
        self::assertStringContainsString('$registration->mode', $root === '' ? '' : (string) file_get_contents(
            $root.'/app/Application/PublicProjectionWorker/PublicProjectionDeliveryConsumerRegistry.php',
        ));
        self::assertStringNotContainsString('AccountStatus', $worker);
    }

    public function test_migration_043_is_owner_only_additive_and_reversible(): void
    {
        $root = dirname(__DIR__, 2);
        $migrationRoot = $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/';
        $up = (string) file_get_contents($migrationRoot.'043_identity_access_outbox_owner.sql');
        $down = (string) file_get_contents($migrationRoot.'043_identity_access_outbox_owner.down.sql');

        self::assertSame(4, substr_count($up, 'CREATE TABLE IF NOT EXISTS identity_access.'));
        self::assertSame(4, substr_count($down, 'DROP TABLE IF EXISTS identity_access.'));
        foreach (['routing_destination', 'routing_version', 'routing_checksum'] as $column) {
            self::assertStringContainsString($column, $up);
        }
        foreach (['041_account_status_lifecycle_workflow.sql', '042_historical_account_persistence.sql'] as $frozen) {
            self::assertStringNotContainsString(
                'public_projection_outbox',
                (string) file_get_contents(
                    $root.'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/'.$frozen,
                ),
            );
        }
    }

    public function test_no_identity_access_specialized_outbox_component_exists(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['Writer', 'Reader', 'Mapper', 'Worker', 'Adapter'] as $suffix) {
            self::assertFileDoesNotExist(
                $root.'/app/Infrastructure/IdentityAccessOutbox/IdentityAccessOutbox'.$suffix.'.php',
            );
            self::assertFileDoesNotExist(
                $root.'/app/Infrastructure/AccountStatusOutbox/AccountStatusOutbox'.$suffix.'.php',
            );
        }
    }
}
