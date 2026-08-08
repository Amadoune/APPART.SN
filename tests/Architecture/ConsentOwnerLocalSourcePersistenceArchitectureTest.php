<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConsentOwnerLocalSourcePersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function application_contract_is_owner_scoped_and_framework_agnostic(): void
    {
        $root = dirname(__DIR__, 2);
        $application = $root.'/src/Modules/ContactsLeads/Application/ConsentOwnerSource';
        $files = $this->phpFiles($application);

        self::assertNotEmpty($files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            self::assertStringContainsString('namespace Appart\\Modules\\ContactsLeads\\Application\\ConsentOwnerSource', $contents);
            foreach (['PDO', 'Illuminate\\', 'Laravel', 'PostgreSql', 'Repository', 'Runtime', 'Http\\', 'Event\\', 'Outbox'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    #[Test]
    public function migration_is_additive_owner_local_and_preserves_historical_migrations(): void
    {
        $root = dirname(__DIR__, 2);
        $migrations = $root.'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/';
        $up = (string) file_get_contents($migrations.'072_consent_owner_local_source.sql');
        $down = (string) file_get_contents($migrations.'072_consent_owner_local_source.down.sql');

        self::assertStringContainsString('contacts_leads.consent_decision_revisions', $up);
        self::assertStringContainsString('PRIMARY KEY (lead_ingress_intent_id, revision)', $up);
        self::assertStringContainsString('UNIQUE (lead_ingress_intent_id, effective_at)', $up);
        self::assertStringContainsString('revision_checksum', $up);
        self::assertStringContainsString('DROP TABLE IF EXISTS contacts_leads.consent_decision_revisions', $down);
        foreach (['REFERENCES', 'CASCADE', 'TRIGGER', 'identity_access', 'listing_lifecycle', 'professional', 'moderation_reports', 'administration_audit'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $up);
        }
        foreach (['022_lead_lifecycle_workflow.sql', '023_lead_eligibility_source_data.sql', '024_lead_lifecycle_context.sql'] as $historical) {
            self::assertFileExists($migrations.$historical);
        }
    }

    #[Test]
    public function infrastructure_does_not_create_runtime_http_event_delivery_or_outbox(): void
    {
        $root = dirname(__DIR__, 2);
        $sourceRoot = $root.'/src/Modules/ContactsLeads';
        $newFiles = [
            $sourceRoot.'/Infrastructure/Persistence/ConsentOwnerSourceMapper.php',
            $sourceRoot.'/Infrastructure/Persistence/PostgreSql/PostgreSqlConsentOwnerSource.php',
        ];
        foreach ($newFiles as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate\\', 'App\\Http', 'Provider', 'RuntimeHealth', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    /** @return list<string> */
    private function phpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
