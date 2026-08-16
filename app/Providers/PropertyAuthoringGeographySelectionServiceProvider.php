<?php

namespace App\Providers;

use App\Application\PropertyAuthoringGeographySelection\Contract\GeographySelectionReplayValidatorV1;
use App\Application\PropertyAuthoringGeographySelection\DeterministicGeographySelectionReplayValidatorV1;
use Illuminate\Support\ServiceProvider;

final class PropertyAuthoringGeographySelectionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicGeographySelectionReplayValidatorV1::class);
        $this->app->alias(DeterministicGeographySelectionReplayValidatorV1::class, GeographySelectionReplayValidatorV1::class);
    }
}
