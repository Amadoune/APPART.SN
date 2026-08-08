<?php

namespace Tests\Feature;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\AccessibilityComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\EndToEndReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\PerformanceReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ReleaseCandidateReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ResponsiveComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserAcceptanceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserExperienceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\EndToEndReadinessResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\EndToEndReadinessStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\PerformanceReadinessResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\PerformanceReadinessStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ReleaseCandidateResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ReleaseCandidateStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ResponsiveComplianceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ResponsiveComplianceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserAcceptanceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserAcceptanceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserExperienceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserExperienceStatusV1;
use Tests\TestCase;

final class ExperienceAcceptanceHttpFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(ResponsiveComplianceReaderV1::class, new class implements ResponsiveComplianceReaderV1
        {
            public function read(ExperienceAcceptanceObservedAt $observedAt): ResponsiveComplianceResultV1
            {
                return new ResponsiveComplianceResultV1(ResponsiveComplianceStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(AccessibilityComplianceReaderV1::class, new class implements AccessibilityComplianceReaderV1
        {
            public function read(ExperienceAcceptanceObservedAt $observedAt): AccessibilityComplianceResultV1
            {
                return new AccessibilityComplianceResultV1(AccessibilityComplianceStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(UserExperienceReaderV1::class, new class implements UserExperienceReaderV1
        {
            public function read(ExperienceAcceptanceObservedAt $observedAt): UserExperienceResultV1
            {
                return new UserExperienceResultV1(UserExperienceStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(EndToEndReadinessReaderV1::class, new class implements EndToEndReadinessReaderV1
        {
            public function read(ExperienceAcceptanceObservedAt $observedAt): EndToEndReadinessResultV1
            {
                return new EndToEndReadinessResultV1(EndToEndReadinessStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(PerformanceReadinessReaderV1::class, new class implements PerformanceReadinessReaderV1
        {
            public function read(ExperienceAcceptanceObservedAt $observedAt): PerformanceReadinessResultV1
            {
                return new PerformanceReadinessResultV1(PerformanceReadinessStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(UserAcceptanceReaderV1::class, new class implements UserAcceptanceReaderV1
        {
            public function read(ExperienceAcceptanceObservedAt $observedAt): UserAcceptanceResultV1
            {
                return new UserAcceptanceResultV1(UserAcceptanceStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(ReleaseCandidateReaderV1::class, new class implements ReleaseCandidateReaderV1
        {
            public function read(ExperienceAcceptanceObservedAt $observedAt): ReleaseCandidateResultV1
            {
                return new ReleaseCandidateResultV1(ReleaseCandidateStatusV1::Available, $observedAt);
            }
        });
    }

    public function test_seven_public_endpoints_consume_only_public_readers(): void
    {
        $query = '?observedAt=2026-08-08T10%3A00%3A00.123456%2B00%3A00';
        foreach ([
            'responsive-compliance',
            'accessibility-compliance',
            'user-experience',
            'end-to-end-readiness',
            'performance-readiness',
            'user-acceptance',
            'release-candidate',
        ] as $path) {
            $this->getJson('/api/experience-acceptance/'.$path.$query)
                ->assertOk()
                ->assertExactJson(['status' => 'available', 'observedAt' => '2026-08-08T10:00:00.123456Z'])
                ->assertHeader('Cache-Control', 'no-store, private')
                ->assertHeader('X-Content-Type-Options', 'nosniff');
        }
    }

    public function test_requests_reject_missing_and_unknown_inputs(): void
    {
        $this->getJson('/api/experience-acceptance/responsive-compliance')->assertUnprocessable();
        $this->getJson('/api/experience-acceptance/release-candidate?observedAt=2026-08-08T10%3A00%3A00.123456%2B00%3A00&score=100')->assertUnprocessable();
    }
}
