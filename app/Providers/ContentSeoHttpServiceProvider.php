<?php

namespace App\Providers;

use App\Http\ContentSeo\ContentSeoResponseFactory;
use App\Http\Controllers\EditorialContentController;
use App\Http\Controllers\OperationalSeoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class ContentSeoHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentSeoResponseFactory::class);
        $this->app->singleton(EditorialContentController::class);
        $this->app->singleton(OperationalSeoController::class);
    }

    public function boot(): void
    {
        Route::get('/api/content-seo/editorial-content', EditorialContentController::class)->name('content-seo.editorial-content');
        Route::get('/api/content-seo/operational-seo', OperationalSeoController::class)->name('content-seo.operational-seo');
    }
}
