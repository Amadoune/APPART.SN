<?php

namespace App\Providers;

use App\Application\ActiveGenerationBootstrap\Contract\ActiveGenerationBootstrapStateReader;
use App\Application\ActiveGenerationBootstrap\Contract\BootstrapActiveProjectionGenerationV1;
use App\Application\ActiveGenerationBootstrap\DeterministicActiveProjectionGenerationBootstrapV1;
use App\Infrastructure\ActiveGenerationBootstrap\PostgreSqlActiveGenerationBootstrapStateReader;
use Illuminate\Support\ServiceProvider;

final class ActiveGenerationBootstrapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ActiveGenerationBootstrapStateReader::class, PostgreSqlActiveGenerationBootstrapStateReader::class);
        $this->app->singleton(DeterministicActiveProjectionGenerationBootstrapV1::class);
        $this->app->alias(DeterministicActiveProjectionGenerationBootstrapV1::class, BootstrapActiveProjectionGenerationV1::class);
    }
}
