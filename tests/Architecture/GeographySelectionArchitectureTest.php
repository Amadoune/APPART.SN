<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class GeographySelectionArchitectureTest extends TestCase
{
    public function test_selection_is_a_dedicated_read_only_boundary(): void
    {
        $root = dirname(__DIR__, 2);
        $source = (string) file_get_contents($root.'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/PostgreSqlGeographySelectionSource.php');
        $registry = (string) file_get_contents($root.'/src/Modules/Geography/Application/Contract/PlaceRegistry.php');

        self::assertStringContainsString('implements GeographySelectionSource', $source);
        self::assertStringNotContainsString('INSERT ', $source);
        self::assertStringNotContainsString('UPDATE ', $source);
        self::assertStringNotContainsString('DELETE ', $source);
        self::assertStringNotContainsString('GeographySelection', $registry);
        self::assertStringNotContainsString('PropertyAuthoring', $source);
        self::assertStringNotContainsString('PublicProjection', $source);
        self::assertStringNotContainsString('Search', $source);
    }
}
