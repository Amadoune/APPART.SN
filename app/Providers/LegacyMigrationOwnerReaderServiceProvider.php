<?php

namespace App\Providers;

use Appart\Modules\LegacyMigration\Application\OwnerReader\Contract\LegacyMigrationOwnerReaderV1;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationCutoverOwnerReader;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationInventoryOwnerReader;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationOwnerReaderPolicy;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationQuarantineOwnerReader;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationReconciliationOwnerReader;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationWaveOwnerReader;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationCutoverReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationInventoryReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationQuarantineReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationReconciliationReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationWaveReaderV1;
use Illuminate\Support\ServiceProvider;

final class LegacyMigrationOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LegacyMigrationOwnerReaderPolicy::class);
        $this->app->alias(LegacyMigrationOwnerReaderPolicy::class, LegacyMigrationOwnerReaderV1::class);
        $this->app->singleton(LegacyMigrationInventoryOwnerReader::class);
        $this->app->alias(LegacyMigrationInventoryOwnerReader::class, LegacyMigrationInventoryReaderV1::class);
        $this->app->singleton(LegacyMigrationWaveOwnerReader::class);
        $this->app->alias(LegacyMigrationWaveOwnerReader::class, LegacyMigrationWaveReaderV1::class);
        $this->app->singleton(LegacyMigrationReconciliationOwnerReader::class);
        $this->app->alias(LegacyMigrationReconciliationOwnerReader::class, LegacyMigrationReconciliationReaderV1::class);
        $this->app->singleton(LegacyMigrationQuarantineOwnerReader::class);
        $this->app->alias(LegacyMigrationQuarantineOwnerReader::class, LegacyMigrationQuarantineReaderV1::class);
        $this->app->singleton(LegacyMigrationCutoverOwnerReader::class);
        $this->app->alias(LegacyMigrationCutoverOwnerReader::class, LegacyMigrationCutoverReaderV1::class);
    }
}
