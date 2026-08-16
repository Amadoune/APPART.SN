<?php

namespace Tests\Feature;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceRepository;
use Tests\TestCase;

final class GeographyPlacePersistenceBindingTest extends TestCase
{
    public function test_place_registry_resolves_to_postgresql_repository(): void
    {
        self::assertInstanceOf(PostgreSqlPlaceRepository::class, $this->app->make(PlaceRegistry::class));
        self::assertSame($this->app->make(PlaceRegistry::class), $this->app->make(PlaceRegistry::class));
    }
}
