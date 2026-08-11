<?php

namespace App\Providers;

use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Http\Controllers\PublicSearchResultsController;
use App\Http\PublicSearchResults\PublicSearchResultsResponseFactory;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionMapper;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicSearchResultsReader;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use PDO;

final class PublicSearchResultsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlPublicSearchResultsReader::class, static fn ($app): PostgreSqlPublicSearchResultsReader => new PostgreSqlPublicSearchResultsReader(
            $app->make(PDO::class),
            $app->make(PostgreSqlPublicListingProjectionMapper::class),
        ));
        $this->app->alias(PostgreSqlPublicSearchResultsReader::class, PublicSearchResultsReaderV1::class);
        $this->app->singleton(PublicSearchResultsResponseFactory::class);
        $this->app->singleton(PublicSearchResultsController::class);
    }

    public function boot(): void
    {
        Route::get('/api/public-search/results', PublicSearchResultsController::class)
            ->name('public-search.results');
    }
}
