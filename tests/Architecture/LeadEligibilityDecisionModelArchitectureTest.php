<?php

namespace Tests\Architecture;

use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use PHPUnit\Framework\TestCase;

final class LeadEligibilityDecisionModelArchitectureTest extends TestCase
{
    public function test_historical_decision_sets_are_the_only_closed_matrices(): void
    {
        self::assertSame(
            ['contactable', 'missing', 'not_published', 'closed'],
            array_column(ListingContactability::cases(), 'value'),
        );
        self::assertSame(
            ['eligible_recipient', 'missing', 'suspended', 'not_listing_recipient'],
            array_column(AdvertiserEligibility::cases(), 'value'),
        );

        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads';
        self::assertSame(0, $this->declarationCount($root, 'enum LeadListingEligibility'));
        self::assertSame(0, $this->declarationCount($root, 'enum LeadAdvertiserEligibility'));
    }

    public function test_every_decision_and_relation_has_one_explicit_owner(): void
    {
        $matrix = (string) file_get_contents(dirname(__DIR__, 2).'/docs/LEAD-ELIGIBILITY-DECISION-OWNERSHIP-MATRIX.md');
        self::assertSame(2, preg_match_all('/^\| [^\n|]+ \| ListingLifecycle \|/m', $matrix));
        self::assertSame(2, preg_match_all('/^\| [^\n|]+ \| ContactsLeads \|/m', $matrix));
        self::assertStringContainsString('| Contactabilité Listing | ListingLifecycle |', $matrix);
        self::assertStringContainsString('| Relation Listing–Advertiser | ListingLifecycle |', $matrix);
        self::assertStringContainsString('| Éligibilité à recevoir un Lead | ContactsLeads |', $matrix);
        self::assertStringContainsString('| Révision cohérente du lot | ContactsLeads |', $matrix);
    }

    public function test_positive_and_negative_decisions_are_explicit_and_revisioned(): void
    {
        $listing = (string) file_get_contents(dirname(__DIR__, 2).'/docs/LEAD-LISTING-DECISION-STATE-MATRIX.md');
        $advertiser = (string) file_get_contents(dirname(__DIR__, 2).'/docs/LEAD-ADVERTISER-DECISION-STATE-MATRIX.md');
        $negative = (string) file_get_contents(dirname(__DIR__, 2).'/docs/LEAD-ELIGIBILITY-NEGATIVE-DECISION-POLICY.md');

        foreach (ListingContactability::cases() as $decision) {
            self::assertStringContainsString('`'.$decision->name.'`', $listing);
        }
        foreach (AdvertiserEligibility::cases() as $decision) {
            self::assertStringContainsString('`'.$decision->name.'`', $advertiser);
        }
        self::assertStringContainsString('L\'absence d\'une ligne', $negative);
        self::assertStringContainsString('`EligibilityRevision` complète', $negative);
    }

    public function test_revision_generation_and_materialization_are_fully_explicit_without_implementation(): void
    {
        $root = dirname(__DIR__, 2);
        $revision = (string) file_get_contents($root.'/docs/LEAD-ELIGIBILITY-REVISION-SPECIFICATION.md');
        foreach (['coherenceId', 'version', 'effectiveAt', 'Aucun `now()`', 'UUID aléatoire', 'timestamp PostgreSQL implicite'] as $required) {
            self::assertStringContainsString($required, $revision);
        }

        $blueprint = (string) file_get_contents($root.'/docs/LEAD-ELIGIBILITY-MATERIALIZATION-PORT-BLUEPRINT.md');
        foreach (['révisions divergentes', 'version non croissante', 'rejeu divergent', 'idempotent'] as $required) {
            self::assertStringContainsString($required, $blueprint);
        }
        self::assertStringContainsString('Aucune interface PHP', $blueprint);
        self::assertStringNotContainsString('LeadEligibilityMaterialization', (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php'));
    }

    public function test_certified_contracts_remain_unique_after_source_adapter_progression(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertSame(1, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'interface ListingCatalog'));
        self::assertSame(1, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'interface AdvertiserCatalog'));
        self::assertSame(1, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'class LeadEligibilityProof'));
        self::assertSame(0, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'interface ListingEligibilityProof'));
        self::assertSame(0, $this->declarationCount($root.'/src/Modules/ContactsLeads', 'interface AdvertiserEligibilityProof'));
    }

    private function declarationCount(string $directory, string $declaration): int
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $count += substr_count((string) file_get_contents($file->getPathname()), $declaration);
            }
        }

        return $count;
    }
}
