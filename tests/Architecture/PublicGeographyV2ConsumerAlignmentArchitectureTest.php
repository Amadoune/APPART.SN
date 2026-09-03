<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicGeographyV2ConsumerAlignmentArchitectureTest extends TestCase
{
    public function test_v2_contracts_have_no_url_slug_or_search_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $paths = [
            $root.'/app/Application/PublicGeographySource/PublicGeographyDecisionV2.php',
            $root.'/app/Application/PublicGeographySource/PublicGeographyBreadcrumbItemV2.php',
            $root.'/src/Modules/ContentSeo/Domain/Model/PublicGeographySeoSourceV2.php',
            $root.'/src/Modules/ContentSeo/Domain/Model/PublicGeographyBreadcrumbItemV2.php',
        ];

        foreach ($paths as $path) {
            $source = strtolower((string) file_get_contents($path));
            self::assertStringNotContainsString('canonicalurl', $source, $path);
            self::assertStringNotContainsString('searchdiscovery', $source, $path);
            self::assertStringNotContainsString("'/geography/", $source, $path);
            self::assertStringNotContainsString('javascript:', $source, $path);
        }
    }

    public function test_alignment_adds_no_migration(): void
    {
        $matches = glob(dirname(__DIR__, 2).'/database/migrations/*public*geography*v2*');

        self::assertSame([], $matches === false ? [] : $matches);
    }
}
