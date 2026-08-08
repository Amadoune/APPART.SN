<?php

namespace App\Providers;

use App\Http\AdministrationConsole\AdministrationConsoleResponseFactory;
use App\Http\Controllers\AdministrationAuditController;
use App\Http\Controllers\AdministrationOperatorController;
use App\Http\Controllers\AdministrationQueueController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AdministrationConsoleHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdministrationConsoleResponseFactory::class);
        $this->app->singleton(AdministrationOperatorController::class);
        $this->app->singleton(AdministrationQueueController::class);
        $this->app->singleton(AdministrationAuditController::class);
    }

    public function boot(): void
    {
        Route::get('/api/administration/operator', AdministrationOperatorController::class)->name('administration.operator');
        Route::get('/api/administration/queue', AdministrationQueueController::class)->name('administration.queue');
        Route::get('/api/administration/audit', AdministrationAuditController::class)->name('administration.audit');
    }
}
