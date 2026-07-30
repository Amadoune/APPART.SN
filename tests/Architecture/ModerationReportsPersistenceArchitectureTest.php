<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationReportsPersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function application_persistence_contracts_are_owner_scoped_and_framework_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ModerationReports/Application/ModerationPersistence';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                '\\Infrastructure\\',
                'Illuminate\\',
                'PDO',
                'SELECT ',
                'INSERT ',
                'UPDATE ',
                'Repository',
                'Runtime',
                'Http',
                'IdentityAccess',
                'ListingLifecycle',
                'Media\\',
                'Professionals\\',
                'AdministrationAudit',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getPathname());
            }
        }
    }

    #[Test]
    public function migration_is_additive_owner_scoped_and_reversible(): void
    {
        $root = dirname(__DIR__, 2);
        $up = (string) file_get_contents($root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/063_moderation_reports.sql');
        $down = (string) file_get_contents($root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/063_moderation_reports.down.sql');

        self::assertStringContainsString('CREATE SCHEMA IF NOT EXISTS moderation_reports', $up);
        foreach (['cases', 'report_revisions', 'finding_revisions', 'decision_revisions', 'decision_supersessions', 'case_intents', 'queue_items', 'queue_checkpoints'] as $table) {
            self::assertStringContainsString("moderation_reports.{$table}", $up);
            self::assertStringContainsString("moderation_reports.{$table}", $down);
        }
        self::assertStringNotContainsString('FOREIGN KEY', strtoupper($up));
        self::assertStringNotContainsString('CASCADE', strtoupper($up));
        self::assertStringNotContainsString('identity_access.', $up);
        self::assertStringNotContainsString('listing_lifecycle.', $up);
        self::assertStringNotContainsString('media.', $up);
        self::assertStringNotContainsString('professionals.', $up);
        self::assertStringNotContainsString('administration_audit.', $up);
        self::assertStringContainsString('DROP SCHEMA IF EXISTS moderation_reports', $down);
    }

    #[Test]
    public function sprint_introduces_no_runtime_http_event_delivery_or_outbox(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        $migration = (string) file_get_contents($root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/063_moderation_reports.sql');

        self::assertStringNotContainsString('ModerationReports', $providers);
        self::assertStringNotContainsString('outbox', strtolower($migration));
        self::assertDirectoryDoesNotExist($root.'/src/Modules/ModerationReports/Infrastructure/Runtime');
        self::assertDirectoryDoesNotExist($root.'/src/Modules/ModerationReports/Infrastructure/Http');
    }
}
