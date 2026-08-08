<?php

namespace App\Providers;

use App\Http\Controllers\ExperienceAcceptanceAccessibilityComplianceController;
use App\Http\Controllers\ExperienceAcceptanceEndToEndReadinessController;
use App\Http\Controllers\ExperienceAcceptancePerformanceReadinessController;
use App\Http\Controllers\ExperienceAcceptanceReleaseCandidateController;
use App\Http\Controllers\ExperienceAcceptanceResponsiveComplianceController;
use App\Http\Controllers\ExperienceAcceptanceUserAcceptanceController;
use App\Http\Controllers\ExperienceAcceptanceUserExperienceController;
use App\Http\ExperienceAcceptance\ExperienceAcceptanceResponseFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class ExperienceAcceptanceHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ExperienceAcceptanceResponseFactory::class);
        $this->app->singleton(ExperienceAcceptanceResponsiveComplianceController::class);
        $this->app->singleton(ExperienceAcceptanceAccessibilityComplianceController::class);
        $this->app->singleton(ExperienceAcceptanceUserExperienceController::class);
        $this->app->singleton(ExperienceAcceptanceEndToEndReadinessController::class);
        $this->app->singleton(ExperienceAcceptancePerformanceReadinessController::class);
        $this->app->singleton(ExperienceAcceptanceUserAcceptanceController::class);
        $this->app->singleton(ExperienceAcceptanceReleaseCandidateController::class);
    }

    public function boot(): void
    {
        Route::get('/api/experience-acceptance/responsive-compliance', ExperienceAcceptanceResponsiveComplianceController::class)->name('experience-acceptance.responsive-compliance');
        Route::get('/api/experience-acceptance/accessibility-compliance', ExperienceAcceptanceAccessibilityComplianceController::class)->name('experience-acceptance.accessibility-compliance');
        Route::get('/api/experience-acceptance/user-experience', ExperienceAcceptanceUserExperienceController::class)->name('experience-acceptance.user-experience');
        Route::get('/api/experience-acceptance/end-to-end-readiness', ExperienceAcceptanceEndToEndReadinessController::class)->name('experience-acceptance.end-to-end-readiness');
        Route::get('/api/experience-acceptance/performance-readiness', ExperienceAcceptancePerformanceReadinessController::class)->name('experience-acceptance.performance-readiness');
        Route::get('/api/experience-acceptance/user-acceptance', ExperienceAcceptanceUserAcceptanceController::class)->name('experience-acceptance.user-acceptance');
        Route::get('/api/experience-acceptance/release-candidate', ExperienceAcceptanceReleaseCandidateController::class)->name('experience-acceptance.release-candidate');
    }
}
