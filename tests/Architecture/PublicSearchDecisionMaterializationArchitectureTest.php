<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicSearchDecisionMaterializationArchitectureTest extends TestCase
{
    public function test_materializer_remains_search_owned_and_projection_free(): void
    {
        $root = dirname(__DIR__, 2);
        $materializer = (string) file_get_contents($root.'/src/Modules/SearchDiscovery/Application/Materialization/DeterministicPublicSearchDecisionMaterializerV1.php');
        foreach (['PublicListingProjection', 'listing_projections', 'PublicProjectionStore', 'SearchController', 'random_bytes', 'new DateTime', 'SELECT ', 'INSERT ', 'UPDATE ', 'DELETE '] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $materializer);
        }
        self::assertStringContainsString('PublicSearchRankingPolicyV1', $materializer);
        self::assertStringContainsString('SearchProjectionPolicy', $materializer);
        self::assertStringContainsString('SearchDecisionWriter', $materializer);
    }

    public function test_implementation_adds_no_migration_or_search_http_surface(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicSearchDecisionMaterializationServiceProvider.php');
        foreach (['Route::', 'Controller', 'Blade', 'PublicListingProjection', 'Migration'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
        self::assertStringContainsString('PublicSearchRankingPolicyV1::class', $provider);
        self::assertStringContainsString('MaterializePublicSearchDecisionV1::class', $provider);
    }
}
