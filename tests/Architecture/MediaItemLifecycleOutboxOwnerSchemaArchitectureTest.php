<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleOutboxOwnerSchemaArchitectureTest extends TestCase
{
    public function test_historical_migration_already_owns_media_and_remains_the_single_schema_source(): void
    {
        $root = $this->migrationRoot();
        $historical = (string) file_get_contents($root.'/005_public_projection_outbox.sql');

        self::assertStringContainsString("'media'", $historical);
        self::assertSame([], glob($root.'/*media*outbox*') ?: []);
        self::assertSame([], glob($root.'/034_*') ?: []);
    }

    public function test_writer_and_reader_keep_the_single_generic_owner_resolver(): void
    {
        $root = dirname(__DIR__, 2);
        $writer = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxWriter.php');
        $reader = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxReader.php');

        self::assertStringContainsString('PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule)', $writer);
        self::assertStringContainsString('PostgreSqlPublicProjectionOutboxSchema::all()', $reader);
        self::assertStringContainsString('PostgreSqlPublicProjectionOutboxSchema::moduleFor($schema)', $reader);
        self::assertStringContainsString('m.source_module=:owner_module', $reader);
    }

    public function test_no_downstream_media_delivery_component_is_introduced(): void
    {
        $root = dirname(__DIR__, 2);

        foreach (['MediaItemLifecycleDeliveryConsumer', 'MediaItemLifecycleWorker', 'MediaItemLifecycleAtomicEventOrchestrator'] as $class) {
            self::assertSame([], glob($root.'/app/**/*'.$class.'*.php') ?: []);
        }
    }

    private function migrationRoot(): string
    {
        return dirname(__DIR__, 2).'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations';
    }
}
