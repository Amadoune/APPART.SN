<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicSearchExperienceArchitectureTest extends TestCase
{
    public function test_product_search_depends_only_on_public_reader_and_query(): void
    {
        $controller = $this->contents('app/Http/Controllers/PublicSearchExperienceController.php');
        $view = $this->contents('resources/views/public-search-results.blade.php');

        self::assertStringContainsString('PublicSearchResultsReaderV1', $controller);
        self::assertStringContainsString('PublicSearchResultsRequest', $controller);
        foreach (['ListingDraft', 'Authoring', 'Aggregate', 'PDO', 'DB::', 'PostgreSql'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller);
            self::assertStringNotContainsString($forbidden, $view);
        }
    }

    public function test_search_form_uses_shareable_get_parameters_without_browser_filtering(): void
    {
        $home = $this->contents('resources/views/home.blade.php');
        $view = $this->contents('resources/views/public-search-results.blade.php');

        self::assertStringContainsString('method="GET"', $home);
        self::assertStringContainsString('name="transaction"', $home);
        self::assertStringContainsString('name="city"', $home);
        self::assertStringContainsString('name="propertyType"', $home);
        self::assertStringNotContainsString('data-shell-action>Rechercher', $home);
        self::assertStringNotContainsString('fetch(', $view);
    }

    private function contents(string $path): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));
        self::assertIsString($contents);

        return $contents;
    }
}
