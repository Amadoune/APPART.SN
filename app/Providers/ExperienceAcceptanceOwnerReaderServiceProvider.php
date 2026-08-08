<?php

namespace App\Providers;

use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\AccessibilityComplianceOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\EndToEndReadinessOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\PerformanceReadinessOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\ReleaseCandidateOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\ResponsiveComplianceOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\UserAcceptanceOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\UserExperienceOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\AccessibilityComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\EndToEndReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\PerformanceReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ReleaseCandidateReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ResponsiveComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserAcceptanceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserExperienceReaderV1;
use Illuminate\Support\ServiceProvider;

final class ExperienceAcceptanceOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ResponsiveComplianceOwnerReader::class);
        $this->app->alias(ResponsiveComplianceOwnerReader::class, ResponsiveComplianceReaderV1::class);
        $this->app->singleton(AccessibilityComplianceOwnerReader::class);
        $this->app->alias(AccessibilityComplianceOwnerReader::class, AccessibilityComplianceReaderV1::class);
        $this->app->singleton(UserExperienceOwnerReader::class);
        $this->app->alias(UserExperienceOwnerReader::class, UserExperienceReaderV1::class);
        $this->app->singleton(EndToEndReadinessOwnerReader::class);
        $this->app->alias(EndToEndReadinessOwnerReader::class, EndToEndReadinessReaderV1::class);
        $this->app->singleton(PerformanceReadinessOwnerReader::class);
        $this->app->alias(PerformanceReadinessOwnerReader::class, PerformanceReadinessReaderV1::class);
        $this->app->singleton(UserAcceptanceOwnerReader::class);
        $this->app->alias(UserAcceptanceOwnerReader::class, UserAcceptanceReaderV1::class);
        $this->app->singleton(ReleaseCandidateOwnerReader::class);
        $this->app->alias(ReleaseCandidateOwnerReader::class, ReleaseCandidateReaderV1::class);
    }
}
