<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicDiscoverabilityArchitectureTest extends TestCase
{
    public function test_sitemap_depends_only_on_public_read_contracts(): void
    {
        $controller = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/PublicSitemapController.php');

        self::assertStringContainsString('PublicSearchResultsReaderV1', $controller);
        self::assertStringContainsString('PublicListingQuery', $controller);
        foreach (['PDO', 'DB::', 'PostgreSql', 'ListingRegistry', 'PropertyRegistry', 'IdentityAccess'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller);
        }
    }

    public function test_robots_keeps_private_and_internal_surfaces_out_of_indexing(): void
    {
        $robots = (string) file_get_contents(dirname(__DIR__, 2).'/public/robots.txt');

        foreach (['/api/', '/authoring/', '/espace-proprietaire', '/professional/', '/_local/'] as $privatePath) {
            self::assertStringContainsString('Disallow: '.$privatePath, $robots);
        }
        self::assertStringContainsString('Sitemap: https://appart.sn/sitemap.xml', $robots);
    }
}
