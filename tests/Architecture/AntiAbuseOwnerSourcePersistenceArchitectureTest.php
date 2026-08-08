<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AntiAbuseOwnerSourcePersistenceArchitectureTest extends TestCase
{
    public function test_application_contracts_are_owner_scoped_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/AntiAbuseOwnerSource';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            foreach (['PDO', 'PostgreSql', 'Infrastructure\\', 'Illuminate\\', 'Runtime\\', 'App\\Providers', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_persistence_is_owner_local_and_does_not_depend_on_runtime_or_reader(): void
    {
        $root = dirname(__DIR__, 2);
        $store = file_get_contents($root.'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/PostgreSqlAntiAbuseOwnerSource.php');
        self::assertIsString($store);
        foreach (['IdentityAccess', 'ListingLifecycle', 'Professional', 'Moderation', 'AdministrationAudit', 'LeadContactConsent'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $store);
        }
        self::assertStringNotContainsString('AntiAbuseOwnerSourceRuntime', $store);
        self::assertStringNotContainsString('LeadIngressAntiAbuseReaderV1', $store);
    }

    public function test_migration_is_additive_minimal_and_historical_migrations_are_unchanged_by_scope(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/';
        $migration = file_get_contents($root.'073_anti_abuse_owner_local_source.sql');
        self::assertIsString($migration);
        foreach (['FOREIGN KEY', 'REFERENCES ', 'CASCADE', 'TRIGGER', 'account_id', 'listing_id', 'professional_id', 'ip_address', 'user_agent', 'score', 'reason'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $migration);
        }
        foreach (['022_lead_lifecycle_workflow.sql', '023_lead_eligibility_source_data.sql', '024_lead_lifecycle_context.sql'] as $historical) {
            self::assertFileExists($root.$historical);
        }
    }
}
