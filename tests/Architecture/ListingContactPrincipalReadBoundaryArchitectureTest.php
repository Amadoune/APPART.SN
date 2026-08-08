<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ListingContactPrincipalReadBoundaryArchitectureTest extends TestCase
{
    #[Test]
    public function contract_is_owner_local_read_only_and_infrastructure_free(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/ContactPrincipalBoundary';
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
            'ContactPrincipalObservedAt.php',
            'ListingContactPrincipalReaderV1.php',
            'ListingContactPrincipalResultV1.php',
            'ListingContactPrincipalStatusV1.php',
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
            'Projection',
            'Aggregate',
            'Runtime',
            'Provider',
            'ContactsLeads',
            'Professionals',
            'Moderation',
            'Search',
            'PublicProjection',
            'Event',
            'Outbox',
            'RepresentativeId',
            'MandateId',
            'ProfessionalId',
            'email',
            'phone',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }

        self::assertStringContainsString('interface ListingContactPrincipalReaderV1', $sources);
        self::assertStringContainsString('Opaque canonical AccountId', $sources);
        self::assertStringNotContainsString('Modules\\IdentityAccess', $sources);
        self::assertStringNotContainsString('now()', $sources);
    }

    #[Test]
    public function amendment_introduces_no_implementation_or_runtime_composition(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $runtimeHealth = (string) file_get_contents($root.'/app/Application/RuntimeHealth/RuntimeHealthComponent.php');

        self::assertStringNotContainsString('ListingContactPrincipalReaderV1', $provider);
        self::assertStringNotContainsString('ListingContactPrincipal', $runtimeHealth);
        self::assertFileDoesNotExist(
            $root.'/src/Modules/ListingLifecycle/Application/ContactPrincipalBoundary/OwnerListingContactPrincipalReaderV1.php',
        );
    }
}
