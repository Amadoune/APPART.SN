<?php

namespace App\Providers;

use App\Application\ModerationOperationalAuditEventProduction\OperationalAuditModerationCaseOrchestratorV1;
use App\Application\ModerationOrchestration\Contract\ModerationCaseOrchestratorV1;
use App\Application\ModerationOrchestration\DeterministicModerationCaseOrchestratorV1;
use Illuminate\Support\ServiceProvider;

final class ModerationOrchestrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicModerationCaseOrchestratorV1::class);
        $this->app->singleton(OperationalAuditModerationCaseOrchestratorV1::class);
        $this->app->alias(OperationalAuditModerationCaseOrchestratorV1::class, ModerationCaseOrchestratorV1::class);
    }
}
