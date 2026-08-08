<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ProfessionalLeadRecipientPublicReadArchitectureTest extends TestCase
{
    #[Test]
    public function contract_is_owner_local_decision_only_and_infrastructure_free(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalLeadRecipientPublicRead';
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
            'LeadRecipientObservedAt.php',
            'ProfessionalLeadRecipientReaderV1.php',
            'ProfessionalLeadRecipientResultV1.php',
            'ProfessionalLeadRecipientStatusV1.php',
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
            'ListingLifecycle',
            'IdentityAccess',
            'ContactsLeads',
            'Search',
            'Moderation',
            'Reservation',
            'Favorites',
            'Mandate',
            'Establishment',
            'Verification',
            'Profile',
            'Consent',
            'email',
            'phone',
            'Event',
            'Delivery',
            'Outbox',
            'now()',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }

        self::assertStringContainsString('interface ProfessionalLeadRecipientReaderV1', $sources);
        self::assertStringContainsString('ProfessionalId $professionalId', $sources);
    }

    #[Test]
    public function amendment_introduces_no_implementation_or_runtime_binding(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = '';

        foreach (glob($root.'/app/Providers/*.php') ?: [] as $provider) {
            $providers .= (string) file_get_contents($provider);
        }

        self::assertStringNotContainsString('ProfessionalLeadRecipientReaderV1', $providers);
        self::assertFileDoesNotExist(
            $root.'/src/Modules/Professionals/Infrastructure/Runtime/OwnerProfessionalLeadRecipientReaderV1.php',
        );
    }
}
