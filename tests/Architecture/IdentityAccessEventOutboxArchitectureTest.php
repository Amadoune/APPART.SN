<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAccessEventOutboxArchitectureTest extends TestCase
{
    #[Test]
    public function outbox_contracts_and_integration_have_no_http_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        foreach ([
            $root.'/app/Application/IdentityAccessEventOutbox',
            $root.'/app/Application/IdentityAccessEventIntegration',
        ] as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $source = (string) file_get_contents($file->getPathname());
                foreach (['Controller', 'Middleware', 'Route::', 'Request\\'] as $forbidden) {
                    self::assertStringNotContainsString($forbidden, $source);
                }
            }
        }
        $infrastructure = (string) file_get_contents(
            $root.'/app/Infrastructure/IdentityAccessEventOutbox/PostgreSql/PostgreSqlIdentityAccessOutbox.php',
        );
        foreach (['Controller', 'Middleware', 'Route::', 'Request\\'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $infrastructure);
        }
    }

    #[Test]
    public function migration_054_is_additive_owner_scoped_and_does_not_touch_frozen_outbox(): void
    {
        $migration = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/054_identity_access_event_outbox.sql',
        );

        self::assertStringContainsString('identity_access_completion.event_outbox_messages', $migration);
        self::assertStringContainsString('identity_access_completion.event_outbox_deliveries', $migration);
        self::assertStringNotContainsString('ALTER TABLE', strtoupper($migration));
        self::assertStringNotContainsString('identity_access.public_projection_outbox', $migration);
        self::assertStringNotContainsString('CASCADE', strtoupper($migration));
    }
}
