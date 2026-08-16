<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class GeographyPlacePersistenceArchitectureTest extends TestCase
{
    public function test_repository_is_geography_infrastructure_and_preserves_boundaries(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/PostgreSqlPlaceRepository.php');
        $registry = (string) file_get_contents($root.'/src/Modules/Geography/Application/Contract/PlaceRegistry.php');

        self::assertStringContainsString('implements PlaceRegistry', $repository);
        self::assertStringNotContainsString('PublicProjection', $repository);
        self::assertStringNotContainsString('PropertyAuthoring', $repository);
        self::assertStringNotContainsString('GeographySelection', $registry);
        self::assertStringNotContainsString('paginate', $registry);
    }

    public function test_migration_contains_no_data_promotion(): void
    {
        $sql = strtolower((string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/098_geography_places.sql'));
        self::assertStringNotContainsString('insert into', $sql);
        self::assertStringNotContainsString('public_geography', $sql);
        self::assertStringNotContainsString('place_lifecycle', $sql);
    }
}
