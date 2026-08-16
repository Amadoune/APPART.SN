<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationCommandGatewayArchitectureTest extends TestCase
{
    public function test_gateway_contracts_are_framework_and_persistence_free(): void
    {
        $root = dirname(__DIR__, 2);
        $path = $root.'/src/Modules/ListingLifecycle/Application/PublicationGateway';
        foreach (glob($path.'/**/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['PDO', 'PostgreSql', 'Illuminate\\', 'Search', 'Projection', 'PublicationReview', 'Controller'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_gateway_has_required_atomicity_and_no_application_sql(): void
    {
        $root = dirname(__DIR__, 2);
        $gateway = (string) file_get_contents($root.'/app/Application/ListingPublicationCommandGateway/DeterministicListingPublicationCommandGateway.php');
        foreach (['ListingPublicationGatewayTransaction', 'ListingPublicationCommandLedgerV1', 'SendToReview', 'PublishListing', 'ListingPublicationWorkflowStore'] as $required) {
            self::assertStringContainsString($required, $gateway);
        }
        foreach (['SELECT ', 'INSERT ', 'UPDATE ', 'DELETE ', 'PDO'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $gateway);
        }
    }

    public function test_begin_review_uses_the_owner_scoped_authoring_property_catalog(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/ListingPublicationCommandGatewayServiceProvider.php');

        self::assertStringContainsString('needs(SendToReview::class)', $provider);
        self::assertStringContainsString('PropertyAuthoringCatalogAdapter::class', $provider);
    }

    public function test_approve_uses_the_owner_scoped_authoring_property_catalog(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/ListingPublicationCommandGatewayServiceProvider.php');

        self::assertStringContainsString('needs(PublishListing::class)', $provider);
        self::assertSame(2, substr_count($provider, 'PropertyAuthoringCatalogAdapter::class'));
    }
}
