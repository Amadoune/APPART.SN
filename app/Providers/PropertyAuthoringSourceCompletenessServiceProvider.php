<?php

namespace App\Providers;

use App\Application\PropertyAuthoringSourceCompleteness\Contract\PropertyAuthoringStateEnricherV1;
use App\Application\PropertyAuthoringSourceCompleteness\DeterministicPropertyAuthoringStateEnricherV1;
use Illuminate\Support\ServiceProvider;

final class PropertyAuthoringSourceCompletenessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicPropertyAuthoringStateEnricherV1::class);
        $this->app->alias(DeterministicPropertyAuthoringStateEnricherV1::class, PropertyAuthoringStateEnricherV1::class);
    }
}
