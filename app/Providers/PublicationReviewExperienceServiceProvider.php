<?php

namespace App\Providers;

use App\Application\PublicationReviewExperience\Contract\PublicationReviewExperienceV1;
use App\Application\PublicationReviewExperience\DeterministicPublicationReviewExperience;
use Illuminate\Support\ServiceProvider;

final class PublicationReviewExperienceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PublicationReviewExperienceV1::class, DeterministicPublicationReviewExperience::class);
    }
}
