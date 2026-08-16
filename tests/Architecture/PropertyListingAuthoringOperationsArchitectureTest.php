<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyListingAuthoringOperationsArchitectureTest extends TestCase
{
    public function test_operations_application_depends_only_on_public_application_contracts(): void
    {
        $directory = dirname(__DIR__, 2).'/app/Application/PropertyListingAuthoringOperations';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            self::assertStringNotContainsString('\\Infrastructure\\', $source);
            self::assertStringNotContainsString('Illuminate\\', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('SELECT ', $source);
        }
    }

    public function test_handoff_uses_create_listing_v1_and_event_publication_orchestrator_contracts(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/PropertyListingAuthoringOperations/DeterministicPropertyListingAuthoringOperations.php',
        );

        self::assertStringContainsString('CreateListingDraftV1', $source);
        self::assertStringContainsString('ListingPublicationEventOrchestrator', $source);
        self::assertStringContainsString('ListingPublicationEventMetadata', $source);
        self::assertStringContainsString('ListingPublicationAction::Submit', $source);
        self::assertStringContainsString('throw new AuthoringOperationRollback', $source);
        self::assertStringContainsString('private function committable', $source);
        self::assertStringNotContainsString('ListingPublicationWorkflowStore', $source);
        self::assertStringNotContainsString('PostgreSqlListingPublication', $source);
    }

    public function test_provider_is_additive_and_frozen_catalogue_remains_unchanged(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PropertyListingAuthoringOperationsServiceProvider.php');
        $requirements = (string) file_get_contents($root.'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');

        self::assertStringContainsString('PropertyListingAuthoringOperations::class', $provider);
        self::assertStringContainsString('ListingPublicationEventOrchestrator::class', $provider);
        foreach (['Controller', 'Route', 'Middleware', 'Delivery', 'Outbox', 'RuntimeHealth'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
    }
}
