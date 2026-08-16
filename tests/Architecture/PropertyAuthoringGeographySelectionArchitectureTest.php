<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyAuthoringGeographySelectionArchitectureTest extends TestCase
{
    public function test_http_and_replay_depend_only_on_f1_reader(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = (string) file_get_contents($root.'/app/Http/Controllers/PropertyAuthoringGeographySelectionController.php');
        $validator = (string) file_get_contents($root.'/app/Application/PropertyAuthoringGeographySelection/DeterministicGeographySelectionReplayValidatorV1.php');

        self::assertStringContainsString('GeographySelectionReaderV1', $controller);
        self::assertStringContainsString('GeographySelectionReaderV1', $validator);
        foreach (['PDO', 'PostgreSql', 'PlaceRegistry', 'Projection', 'Search', 'INSERT ', 'UPDATE ', 'DELETE '] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller.$validator);
        }
    }

    public function test_f4_reopening_reuses_the_closed_f4_a_boundary(): void
    {
        $root = dirname(__DIR__, 2);
        $state = (string) file_get_contents($root.'/src/Modules/RealEstateCatalog/Application/AuthoringPersistence/PropertyAuthoringState.php');
        $routes = (string) file_get_contents($root.'/routes/web.php');

        self::assertStringContainsString('geographicPlaceId', $state);
        self::assertStringContainsString('addressIntentId', $state);
        self::assertSame(1, substr_count($routes, "Route::get('/geography/selections'"));
        self::assertFileExists($root.'/src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/099_property_authoring_source_completeness.sql');
    }

    public function test_workspace_uses_real_hierarchy_and_preserves_replay_proof(): void
    {
        $root = dirname(__DIR__, 2);
        $view = (string) file_get_contents($root.'/resources/views/authoring-workspace.blade.php');
        $script = (string) file_get_contents($root.'/resources/js/authoring.js');

        foreach (['geographicPlaceId', 'geographicPlaceType', 'geographicParentPlaceId', 'geographicSelectionCursor', 'geographicSelectionLimit'] as $field) {
            self::assertStringContainsString('name="'.$field.'"', $view);
        }
        foreach (['country', 'region', 'department', 'city', 'district', 'neighborhood', 'AbortController', 'nextCursor'] as $capability) {
            self::assertStringContainsString($capability, $script);
        }
        self::assertStringNotContainsString('place:dakar', $script);
    }
}
