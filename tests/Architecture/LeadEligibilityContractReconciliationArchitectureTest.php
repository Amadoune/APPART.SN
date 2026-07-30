<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class LeadEligibilityContractReconciliationArchitectureTest extends TestCase
{
    public function test_historical_contract_inventory_is_exact_and_unique_inside_contacts_leads(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertFileExists($root.'/src/Modules/ContactsLeads/Application/Contract/ListingCatalog.php');
        self::assertFileExists($root.'/src/Modules/ContactsLeads/Application/Contract/AdvertiserCatalog.php');
        self::assertFileExists($root.'/src/Modules/ContactsLeads/Domain/ValueObject/ListingContactEvidence.php');
        self::assertFileExists($root.'/src/Modules/ContactsLeads/Domain/ValueObject/AdvertiserEligibilityEvidence.php');
        self::assertFileExists($root.'/src/Modules/ContactsLeads/Domain/ValueObject/LeadEligibilityProof.php');

        self::assertSame(1, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'interface ListingCatalog'));
        self::assertSame(1, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'interface AdvertiserCatalog'));
        self::assertSame(1, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'class LeadEligibilityProof'));
        self::assertSame(0, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'class ListingEligibilityProof'));
        self::assertSame(0, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'class AdvertiserEligibilityProof'));
    }

    public function test_production_consumers_are_exhaustively_mapped(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads';
        self::assertSame(
            [
                'Application/UseCase/CreateLead.php',
                'Domain/Model/Lead.php',
            ],
            $this->relativeFilesContaining($root, 'use Appart\\Modules\\ContactsLeads\\Domain\\ValueObject\\LeadEligibilityProof;'),
        );
        self::assertSame(['Application/UseCase/CreateLead.php'], $this->relativeFilesContaining($root, '->contactabilityOf('));
        self::assertSame(['Application/UseCase/CreateLead.php'], $this->relativeFilesContaining($root, '->eligibilityFor('));
    }

    public function test_reconciliation_contracts_remain_unique_after_certified_adapter_progression(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'alias(MaterializedListingCatalog::class, ListingCatalog::class)'));
        self::assertSame(1, substr_count($provider, 'alias(MaterializedAdvertiserCatalog::class, AdvertiserCatalog::class)'));
        self::assertStringNotContainsString('LeadEligibilityProof', $provider);
    }

    public function test_roadmap_selects_reuse_without_ambiguous_coexistence(): void
    {
        $decision = (string) file_get_contents(dirname(__DIR__, 2).'/docs/LEAD-ELIGIBILITY-CONTRACT-RECONCILIATION-DECISION.md');
        self::assertStringContainsString('stratégie 2 — réutilisation des contrats existants', $decision);
        self::assertStringContainsString('aucune seconde famille', $decision);
        self::assertStringContainsString('sans amendement ni dépréciation', $decision);
    }

    private function declarationCount(string $directory, string $declaration): int
    {
        $count = 0;
        foreach ($this->phpFiles($directory) as $file) {
            $count += substr_count((string) file_get_contents($file), $declaration);
        }

        return $count;
    }

    /** @return list<string> */
    private function relativeFilesContaining(string $directory, string $needle): array
    {
        $files = [];
        foreach ($this->phpFiles($directory) as $file) {
            if (str_contains((string) file_get_contents($file), $needle)) {
                $files[] = str_replace('\\', '/', substr($file, strlen($directory) + 1));
            }
        }
        sort($files);

        return $files;
    }

    /** @return list<string> */
    private function phpFiles(string $directory): array
    {
        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
