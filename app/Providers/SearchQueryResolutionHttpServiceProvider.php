<?php

namespace App\Providers;

use App\Http\Controllers\SearchQueryResolutionController;
use App\Http\SearchQueryResolution\SearchQueryResolutionHttpRuntimeV1;
use App\Http\SearchQueryResolution\SearchQueryResolutionResponseFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class SearchQueryResolutionHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchQueryResolutionResponseFactory::class);
        $this->app->singleton(SearchQueryResolutionController::class);
        $this->app->alias(SearchQueryResolutionController::class, SearchQueryResolutionHttpRuntimeV1::class);
    }

    public function boot(): void
    {
        Route::get('/api/search/query-resolution', SearchQueryResolutionController::class)
            ->name('search.query-resolution');
    }
}
