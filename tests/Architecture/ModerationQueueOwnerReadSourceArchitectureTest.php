<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationQueueOwnerReadSourceArchitectureTest extends TestCase
{
    #[Test]
    public function application_contract_is_read_only_framework_sql_and_http_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ModerationReports/Application/QueueOwnerReadSource';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                '\\Infrastructure\\', 'Illuminate\\', 'PDO', 'SELECT ', 'INSERT ',
                'UPDATE ', 'DELETE ', 'Controller', 'Route', 'Middleware',
                'Request', 'Resource', 'Event', 'Delivery', 'Outbox',
                'claim(', 'checkpoint(', 'project(',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getPathname());
            }
        }
    }

    #[Test]
    public function foundation_adds_only_the_owner_read_index_and_no_runtime_or_http(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents($root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/070_moderation_queue_owner_read_source.sql');
        $adapter = (string) file_get_contents($root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/PostgreSqlModerationQueueOwnerReadSourceV1.php');

        self::assertStringContainsString('CREATE INDEX IF NOT EXISTS', $migration);
        self::assertStringNotContainsString('CREATE TABLE', $migration);
        self::assertStringNotContainsString('queue_checkpoints', $adapter);
        self::assertStringNotContainsString('queue_claim_intents', $adapter);
        self::assertStringNotContainsString('ModerationQueueStore', $adapter);
        self::assertFileDoesNotExist($root.'/app/Providers/ModerationQueueOwnerReadSourceServiceProvider.php');
        self::assertDirectoryDoesNotExist($root.'/app/Application/ModerationQueueHttp');
    }
}
