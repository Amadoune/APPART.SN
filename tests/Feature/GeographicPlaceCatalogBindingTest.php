<?php

namespace Tests\Feature;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceRepository;
use Appart\Modules\RealEstateCatalog\Application\Contract\GeographicPlaceCatalog;
use Appart\Modules\RealEstateCatalog\Infrastructure\Geography\GeographyBackedGeographicPlaceCatalog;
use Tests\TestCase;

final class GeographicPlaceCatalogBindingTest extends TestCase
{
    public function test_productive_composition_is_singleton_and_geography_backed(): void
    {
        $catalog = $this->app->make(GeographicPlaceCatalog::class);

        self::assertInstanceOf(GeographyBackedGeographicPlaceCatalog::class, $catalog);
        self::assertSame($catalog, $this->app->make(GeographicPlaceCatalog::class));
        self::assertInstanceOf(PostgreSqlPlaceRepository::class, $this->app->make(PlaceRegistry::class));
    }
}
