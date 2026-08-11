<?php

namespace App\Providers;

use App\Application\MediaAuthoringHttp\Contract\MediaAuthoringHttpRuntime;
use App\Application\MediaAuthoringHttp\DeterministicMediaAuthoringHttpRuntime;
use Illuminate\Support\ServiceProvider;

final class MediaAuthoringHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicMediaAuthoringHttpRuntime::class);
        $this->app->alias(DeterministicMediaAuthoringHttpRuntime::class, MediaAuthoringHttpRuntime::class);
    }
}
