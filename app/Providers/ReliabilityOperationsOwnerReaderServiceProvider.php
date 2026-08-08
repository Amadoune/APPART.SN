<?php

namespace App\Providers;

use Appart\Modules\ReliabilityOperations\Application\OwnerReader\AlertingOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\CapacityPlanningOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\ContinuityOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\MaintenanceOperationsOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\ObservabilityOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\OperationalReadinessOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\ServiceHealthOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\AlertingReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\CapacityPlanningReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ContinuityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\MaintenanceOperationsReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ObservabilityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\OperationalReadinessReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ServiceHealthReaderV1;
use Illuminate\Support\ServiceProvider;

final class ReliabilityOperationsOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ObservabilityOwnerReader::class);
        $this->app->alias(ObservabilityOwnerReader::class, ObservabilityReaderV1::class);
        $this->app->singleton(ServiceHealthOwnerReader::class);
        $this->app->alias(ServiceHealthOwnerReader::class, ServiceHealthReaderV1::class);
        $this->app->singleton(AlertingOwnerReader::class);
        $this->app->alias(AlertingOwnerReader::class, AlertingReaderV1::class);
        $this->app->singleton(MaintenanceOperationsOwnerReader::class);
        $this->app->alias(MaintenanceOperationsOwnerReader::class, MaintenanceOperationsReaderV1::class);
        $this->app->singleton(ContinuityOwnerReader::class);
        $this->app->alias(ContinuityOwnerReader::class, ContinuityReaderV1::class);
        $this->app->singleton(CapacityPlanningOwnerReader::class);
        $this->app->alias(CapacityPlanningOwnerReader::class, CapacityPlanningReaderV1::class);
        $this->app->singleton(OperationalReadinessOwnerReader::class);
        $this->app->alias(OperationalReadinessOwnerReader::class, OperationalReadinessReaderV1::class);
    }
}
