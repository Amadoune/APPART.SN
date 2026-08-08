<?php

namespace App\Providers;

use Appart\Modules\AdministrationConsole\Application\OwnerReader\AdministrationAuditOwnerReader;
use Appart\Modules\AdministrationConsole\Application\OwnerReader\AdministrationConsoleOwnerReaderPolicy;
use Appart\Modules\AdministrationConsole\Application\OwnerReader\AdministrationOperatorOwnerReader;
use Appart\Modules\AdministrationConsole\Application\OwnerReader\AdministrationQueueOwnerReader;
use Appart\Modules\AdministrationConsole\Application\OwnerReader\Contract\AdministrationConsoleOwnerReaderV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationAuditReaderV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationOperatorReaderV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationQueueReaderV1;
use Illuminate\Support\ServiceProvider;

final class AdministrationConsoleOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdministrationConsoleOwnerReaderPolicy::class);
        $this->app->alias(AdministrationConsoleOwnerReaderPolicy::class, AdministrationConsoleOwnerReaderV1::class);
        $this->app->singleton(AdministrationOperatorOwnerReader::class);
        $this->app->alias(AdministrationOperatorOwnerReader::class, AdministrationOperatorReaderV1::class);
        $this->app->singleton(AdministrationQueueOwnerReader::class);
        $this->app->alias(AdministrationQueueOwnerReader::class, AdministrationQueueReaderV1::class);
        $this->app->singleton(AdministrationAuditOwnerReader::class);
        $this->app->alias(AdministrationAuditOwnerReader::class, AdministrationAuditReaderV1::class);
    }
}
