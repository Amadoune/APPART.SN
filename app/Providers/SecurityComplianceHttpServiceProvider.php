<?php

namespace App\Providers;

use App\Http\Controllers\ComplianceControlController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\SecretInventoryController;
use App\Http\Controllers\SecurityAuditController;
use App\Http\SecurityCompliance\SecurityComplianceResponseFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class SecurityComplianceHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SecurityComplianceResponseFactory::class);
        $this->app->singleton(SecretInventoryController::class);
        $this->app->singleton(SecurityAuditController::class);
        $this->app->singleton(IncidentController::class);
        $this->app->singleton(PrivacyPolicyController::class);
        $this->app->singleton(ComplianceControlController::class);
    }

    public function boot(): void
    {
        Route::get('/api/security-compliance/secret-inventory', SecretInventoryController::class)->name('security-compliance.secret-inventory');
        Route::get('/api/security-compliance/security-audit', SecurityAuditController::class)->name('security-compliance.security-audit');
        Route::get('/api/security-compliance/incident', IncidentController::class)->name('security-compliance.incident');
        Route::get('/api/security-compliance/privacy-policy', PrivacyPolicyController::class)->name('security-compliance.privacy-policy');
        Route::get('/api/security-compliance/compliance-control', ComplianceControlController::class)->name('security-compliance.compliance-control');
    }
}
