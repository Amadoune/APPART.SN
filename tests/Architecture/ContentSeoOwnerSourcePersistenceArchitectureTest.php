<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ContentSeoOwnerSourcePersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function application_port_is_owner_scoped_and_has_no_external_dependency(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Application/OwnerSource';
        $sources = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), glob($directory.'/*.php') ?: []));

        self::assertStringContainsString('interface ContentSeoOwnerSource', $sources);
        foreach (['ListingLifecycle', 'Media\\', 'Geography', 'SearchDiscovery', 'IdentityAccess', 'PDO', 'PostgreSQL', 'SQL', 'Runtime', 'Provider', 'Controller', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
    }

    #[Test]
    public function migration_is_additive_owner_local_and_append_only(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/';
        $up = (string) file_get_contents($root.'078_editorial_content_operational_seo_owner_source.sql');
        $down = (string) file_get_contents($root.'078_editorial_content_operational_seo_owner_source.down.sql');

        self::assertStringContainsString('editorial_seo_revision_journal', $up);
        self::assertStringContainsString('editorial_seo_current_index', $up);
        self::assertStringContainsString('revision_checksum', $up);
        self::assertStringContainsString('DROP TABLE IF EXISTS', $down);
        foreach (['FOREIGN KEY', 'REFERENCES ', 'CREATE TRIGGER', 'CREATE VIEW'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, strtoupper($up));
        }
    }
}
