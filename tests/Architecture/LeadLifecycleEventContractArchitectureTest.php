<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleEventContractArchitectureTest extends TestCase
{
    public function test_event_contract_has_no_infrastructure_or_personal_contact_data(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/LeadLifecycleEvent';
        $contents = '';
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $contents .= file_get_contents($file->getPathname());
            }
        }

        foreach (['PDO', 'PostgreSql', 'Laravel', 'Outbox', 'Consumer', 'Worker', 'Http', 'ListingCatalog', 'AdvertiserCatalog', 'LeadEligibilityProof', 'visitor', 'email', 'phone', 'message', 'subject', 'consent'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }
}
