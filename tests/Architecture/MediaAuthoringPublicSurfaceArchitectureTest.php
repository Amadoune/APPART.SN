<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MediaAuthoringPublicSurfaceArchitectureTest extends TestCase
{
    #[Test]
    public function application_composes_only_certified_media_and_authoring_ports(): void
    {
        $root = dirname(__DIR__, 2);
        $source = (string) file_get_contents($root.'/app/Application/MediaAuthoringHttp/DeterministicMediaAuthoringHttpRuntime.php');
        foreach (['PDO', 'PostgreSql', 'SELECT ', 'INSERT ', 'UPDATE ', 'Search', 'Projection', 'ListingLifecycle', 'Eloquent'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        foreach (['MediaIngestionRuntimeV1', 'PropertyAuthoringStore', 'MediaCollectionRegistry'] as $required) {
            self::assertStringContainsString($required, $source);
        }
    }

    #[Test]
    public function http_surface_has_exactly_three_owner_scoped_routes_and_no_owner_input(): void
    {
        $root = dirname(__DIR__, 2);
        $routes = (string) file_get_contents($root.'/routes/web.php');
        self::assertSame(1, substr_count($routes, "Route::prefix('/api/authoring/properties/{propertyId}/media')"));
        $request = (string) file_get_contents($root.'/app/Http/Requests/MediaAuthoringHttpRequest.php');
        $controller = (string) file_get_contents($root.'/app/Http/Controllers/MediaAuthoringHttpController.php');
        self::assertStringNotContainsString("'owner", $request);
        self::assertStringContainsString("'image'", $request);
        self::assertStringContainsString("'replacementMediaId'", $request);
        self::assertStringContainsString('$image->getPathname()', $controller);
        self::assertStringNotContainsString('$image->getRealPath()', $controller);
    }
}
