<?php

namespace Tests\Architecture;

use App\Http\Controllers\PublicListingController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PublicWebAdapterArchitectureTest extends TestCase
{
    public function test_controller_has_only_the_three_certified_ports_and_logger_as_dependencies(): void
    {
        $constructor = (new ReflectionClass(PublicListingController::class))->getConstructor();

        self::assertNotNull($constructor);
        self::assertSame(
            ['App\Application\Contract\PublicListingQuery', 'Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier', 'Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver', 'Psr\Log\LoggerInterface'],
            array_map(static fn ($parameter): string => (string) $parameter->getType(), $constructor->getParameters()),
        );
    }

    public function test_controller_knows_no_domain_repository_builder_policy_catalog_or_projection(): void
    {
        $contents = $this->contents($this->controllerPath());

        self::assertDoesNotMatchRegularExpression(
            '/(?:\\\\Infrastructure\\\\|Repository|PDO|\\bSQL\\b|Builder|Policy|Catalog|SearchListingProjection|SeoListingProjection|ListingSeoDecision)/i',
            $contents,
        );
        self::assertStringNotContainsString('PublicListingReadModel', $contents);
        self::assertStringNotContainsString('ListingId', $contents);
    }

    public function test_route_exposes_only_the_canonical_path_and_no_fallback(): void
    {
        $contents = $this->contents($this->routePath());

        self::assertStringContainsString('/{canonicalPath}', $contents);
        self::assertStringContainsString("'annonces/[^/]+'", $contents);
        self::assertStringNotContainsString('ListingId', $contents);
        self::assertStringNotContainsString('canonicalHistory', $contents);
        self::assertSame(1, substr_count($contents, "Route::get('/{canonicalPath}'"));
        self::assertStringNotContainsString('Route::fallback', $contents);
    }

    public function test_blade_reads_only_the_public_read_model_without_reconstruction(): void
    {
        $contents = $this->contents($this->viewPath());

        self::assertStringContainsString('$listing->htmlRobotsDirective', $contents);
        self::assertStringContainsString('$listing->publicJsonLd', $contents);
        self::assertStringContainsString('$listing->canonicalUrl', $contents);
        self::assertStringContainsString('$listing->breadcrumb', $contents);
        self::assertDoesNotMatchRegularExpression(
            '/(?:Repository|Aggregate|Policy|Builder|Projection|ListingSeoDecision|str_replace|json_encode|json_decode|route\s*\()/i',
            $contents,
        );
    }

    private function contents(string $path): string
    {
        $contents = file_get_contents($path);
        self::assertIsString($contents);

        return $contents;
    }

    private function controllerPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'PublicListingController.php';
    }

    private function routePath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php';
    }

    private function viewPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'public-listing.blade.php';
    }
}
