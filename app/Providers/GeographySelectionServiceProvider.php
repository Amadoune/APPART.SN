<?php

namespace App\Providers;

use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionReaderV1;
use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionSource;
use Appart\Modules\Geography\Application\GeographySelection\DeterministicGeographySelectionReader;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlGeographySelectionSource;
use Illuminate\Support\ServiceProvider;

final class GeographySelectionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlGeographySelectionSource::class);
        $this->app->alias(PostgreSqlGeographySelectionSource::class, GeographySelectionSource::class);
        $this->app->singleton(DeterministicGeographySelectionReader::class);
        $this->app->alias(DeterministicGeographySelectionReader::class, GeographySelectionReaderV1::class);
    }
}
