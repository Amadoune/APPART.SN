<?php

namespace Tests\Architecture;

use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\ListingContactabilityDecisionV1;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ListingContactabilityReadBoundaryArchitectureTest extends TestCase
{
    #[Test]
    public function boundary_is_owner_local_public_read_only_and_infrastructure_free(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/ContactabilityBoundary';
        $sources = '';
        $contractSources = '';
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getFilename();
                $source = (string) file_get_contents($file->getPathname());
                $sources .= $source;
                if ($file->getFilename() !== 'OwnerListingContactabilityReaderV1.php') {
                    $contractSources .= $source;
                }
            }
        }

        sort($files);

        self::assertSame([
            'ContactabilityObservedAt.php',
            'ListingContactabilityDecisionV1.php',
            'ListingContactabilityReaderV1.php',
            'OwnerListingContactabilityReaderV1.php',
        ], $files);
        self::assertStringContainsString(
            'interface ListingContactabilityReaderV1',
            $sources,
        );
        self::assertSame(5, count(ListingContactabilityDecisionV1::cases()));

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
            'Event',
            'Outbox',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contractSources);
        }

        foreach ([
            'ContactsLeads',
            'Professionals',
            'ModerationBoundary',
            'ModerationReports',
            'SearchDiscovery',
            'ContentSeo',
            'PublicListingQuery',
            'PublicProjection',
            'Http',
            'Event',
            'Outbox',
            'ContactCoordinates',
            'AdvertiserId',
            'ProfessionalId',
            'ContactMessage',
            'Consent',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }

        self::assertStringContainsString(
            'private ListingPublicationWorkflowStore $workflowStore',
            $sources,
        );
        self::assertStringNotContainsString('->initialize(', $sources);
        self::assertStringNotContainsString('->append(', $sources);
    }

    #[Test]
    public function runtime_binding_is_unique_without_runtime_health_extension(): void
    {
        $provider = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );
        $runtimeHealth = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/RuntimeHealth/RuntimeHealthComponent.php',
        );

        self::assertSame(
            1,
            substr_count($provider, 'singleton(OwnerListingContactabilityReaderV1::class)'),
        );
        self::assertSame(
            1,
            substr_count(
                $provider,
                'alias(OwnerListingContactabilityReaderV1::class, ListingContactabilityReaderV1::class)',
            ),
        );
        self::assertStringNotContainsString('ListingContactability', $runtimeHealth);
    }
}
