<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PremiumPublicListingPageArchitectureTest extends TestCase
{
    public function test_premium_page_remains_a_pure_public_read_model_adapter(): void
    {
        $controller = $this->contents('app/Http/Controllers/PublicListingController.php');
        $view = $this->contents('resources/views/public-listing.blade.php');

        self::assertStringContainsString('PublicListingQuery', $controller);
        foreach (['Authoring', 'ListingDraft', 'ListingRegistry', 'Aggregate', 'PDO', 'DB::', 'PostgreSql'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller);
            self::assertStringNotContainsString($forbidden, $view);
        }
    }

    public function test_contact_and_price_are_never_simulated(): void
    {
        $view = $this->contents('resources/views/public-listing.blade.php');

        self::assertStringContainsString('Prix non communiqué', $view);
        self::assertStringContainsString('disabled', $view);
        self::assertStringContainsString('Bientôt disponible', $view);
        self::assertStringNotContainsString('mailto:', $view);
        self::assertStringNotContainsString('wa.me', $view);
    }

    private function contents(string $path): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));
        self::assertIsString($contents);

        return $contents;
    }
}
