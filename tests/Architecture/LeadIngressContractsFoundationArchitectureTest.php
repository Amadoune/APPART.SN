<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class LeadIngressContractsFoundationArchitectureTest extends TestCase
{
    #[Test]
    public function foundation_contains_contracts_only_and_is_framework_agnostic(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/LeadIngressContracts';
        $sources = '';
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getFilename();
                $sources .= (string) file_get_contents($file->getPathname());
            }
        }

        sort($files);
        self::assertSame([
            'LeadIngressCommandPortV1.php',
            'LeadIngressId.php',
            'LeadIngressIntentId.php',
            'LeadIngressObservedAt.php',
            'LeadIngressOccurredAt.php',
            'LeadIngressPublicErrorV1.php',
            'LeadIngressQueryPortV1.php',
            'LeadIngressReadResultV1.php',
            'LeadIngressReadStatusV1.php',
            'LeadIngressReceiptV1.php',
            'LeadIngressSubmissionResultV1.php',
            'LeadIngressSubmissionStatusV1.php',
            'ReadOwnLeadIngressReceiptV1.php',
            'SubmitLeadIngressV1.php',
        ], $files);

        foreach ([
            'PDO',
            'PostgreSql',
            'Illuminate',
            'Laravel',
            'Infrastructure',
            'Repository',
            'Store',
            'Snapshot',
            'Runtime',
            'Provider',
            'Controller',
            'Http',
            'Event',
            'Delivery',
            'Outbox',
            'Retry',
            'Replay',
            'ListingLifecycle',
            'Professionals',
            'IdentityAccess',
            'Search',
            'Moderation',
            'Reservation',
            'Favorites',
            'now()',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
    }

    #[Test]
    public function certified_owner_boundaries_remain_unchanged_and_unconsumed(): void
    {
        $root = dirname(__DIR__, 2);
        $sources = '';

        $directory = $root.'/src/Modules/ContactsLeads/Application/LeadIngressContracts';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $sources .= (string) file_get_contents($file->getPathname());
            }
        }

        foreach ([
            'ListingContactabilityReaderV1',
            'ListingContactPrincipalReaderV1',
            'ProfessionalLeadRecipientReaderV1',
        ] as $boundary) {
            self::assertStringNotContainsString($boundary, $sources);
        }

        self::assertDirectoryDoesNotExist(
            $root.'/src/Modules/ContactsLeads/Infrastructure/LeadIngress',
        );
        self::assertDirectoryDoesNotExist($root.'/app/Providers/LeadIngress');
    }
}
