<?php

namespace App\Providers;

use App\Application\ProfessionalEndpoint\Contract\ProfessionalEndpointRuntimeV1;
use App\Application\ProfessionalEndpoint\DeterministicProfessionalEndpointRuntimeV1;
use App\Application\ProfessionalEndpoint\ProfessionalEndpointRuntimeHealthInspector;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class ProfessionalProfileHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicProfessionalEndpointRuntimeV1::class);
        $this->app->alias(DeterministicProfessionalEndpointRuntimeV1::class, ProfessionalEndpointRuntimeV1::class);
        $this->app->extend(
            RuntimeHealthInspector::class,
            static fn (RuntimeHealthInspector $baseline, Application $app): RuntimeHealthInspector => new ProfessionalEndpointRuntimeHealthInspector(
                $baseline,
                $app->bound(ProfessionalEndpointRuntimeV1::class),
                $app->make(ProfessionalEndpointRuntimeV1::class),
            ),
        );
    }
}
