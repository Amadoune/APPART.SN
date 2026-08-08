<?php

namespace App\Providers;

use App\Http\Controllers\LegacyMigrationCutoverController;
use App\Http\Controllers\LegacyMigrationInventoryController;
use App\Http\Controllers\LegacyMigrationQuarantineController;
use App\Http\Controllers\LegacyMigrationReconciliationController;
use App\Http\Controllers\LegacyMigrationWaveController;
use App\Http\LegacyMigration\LegacyMigrationResponseFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class LegacyMigrationHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LegacyMigrationResponseFactory::class);
        $this->app->singleton(LegacyMigrationInventoryController::class);
        $this->app->singleton(LegacyMigrationWaveController::class);
        $this->app->singleton(LegacyMigrationReconciliationController::class);
        $this->app->singleton(LegacyMigrationQuarantineController::class);
        $this->app->singleton(LegacyMigrationCutoverController::class);
    }

    public function boot(): void
    {
        Route::get('/api/legacy-migration/inventory', LegacyMigrationInventoryController::class)->name('legacy-migration.inventory');
        Route::get('/api/legacy-migration/wave', LegacyMigrationWaveController::class)->name('legacy-migration.wave');
        Route::get('/api/legacy-migration/reconciliation', LegacyMigrationReconciliationController::class)->name('legacy-migration.reconciliation');
        Route::get('/api/legacy-migration/quarantine', LegacyMigrationQuarantineController::class)->name('legacy-migration.quarantine');
        Route::get('/api/legacy-migration/cutover', LegacyMigrationCutoverController::class)->name('legacy-migration.cutover');
    }
}
