<?php

namespace App\Providers;

use App\Http\Controllers\ReliabilityOperationsAlertingController;
use App\Http\Controllers\ReliabilityOperationsCapacityPlanningController;
use App\Http\Controllers\ReliabilityOperationsContinuityController;
use App\Http\Controllers\ReliabilityOperationsMaintenanceOperationsController;
use App\Http\Controllers\ReliabilityOperationsObservabilityController;
use App\Http\Controllers\ReliabilityOperationsOperationalReadinessController;
use App\Http\Controllers\ReliabilityOperationsServiceHealthController;
use App\Http\ReliabilityOperations\ReliabilityOperationsResponseFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class ReliabilityOperationsHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReliabilityOperationsResponseFactory::class);
        $this->app->singleton(ReliabilityOperationsObservabilityController::class);
        $this->app->singleton(ReliabilityOperationsServiceHealthController::class);
        $this->app->singleton(ReliabilityOperationsAlertingController::class);
        $this->app->singleton(ReliabilityOperationsMaintenanceOperationsController::class);
        $this->app->singleton(ReliabilityOperationsContinuityController::class);
        $this->app->singleton(ReliabilityOperationsCapacityPlanningController::class);
        $this->app->singleton(ReliabilityOperationsOperationalReadinessController::class);
    }

    public function boot(): void
    {
        Route::get('/api/reliability-operations/observability', ReliabilityOperationsObservabilityController::class)->name('reliability-operations.observability');
        Route::get('/api/reliability-operations/service-health', ReliabilityOperationsServiceHealthController::class)->name('reliability-operations.service-health');
        Route::get('/api/reliability-operations/alerting', ReliabilityOperationsAlertingController::class)->name('reliability-operations.alerting');
        Route::get('/api/reliability-operations/maintenance-operations', ReliabilityOperationsMaintenanceOperationsController::class)->name('reliability-operations.maintenance-operations');
        Route::get('/api/reliability-operations/continuity', ReliabilityOperationsContinuityController::class)->name('reliability-operations.continuity');
        Route::get('/api/reliability-operations/capacity-planning', ReliabilityOperationsCapacityPlanningController::class)->name('reliability-operations.capacity-planning');
        Route::get('/api/reliability-operations/operational-readiness', ReliabilityOperationsOperationalReadinessController::class)->name('reliability-operations.operational-readiness');
    }
}
