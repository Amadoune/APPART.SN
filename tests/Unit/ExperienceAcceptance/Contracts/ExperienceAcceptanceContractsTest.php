<?php

namespace Tests\Unit\ExperienceAcceptance\Contracts;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceStatusV1;
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
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceContractsTest extends TestCase
{
    public function test_responsive_compliance_catalogue_and_result_are_closed(): void
    {
        self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_column(ResponsiveComplianceStatusV1::cases(), 'value'));
        foreach (ResponsiveComplianceStatusV1::cases() as $status) {
            $result = new ResponsiveComplianceResultV1($status, self::observedAt());
            self::assertSame($status, $result->status);
            self::assertSame('2026-08-08T10:00:00.123456Z', $result->observedAt);
            self::assertCount(2, get_object_vars($result));
        }
    }

    public function test_accessibility_compliance_catalogue_and_result_are_closed(): void
    {
        self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_column(AccessibilityComplianceStatusV1::cases(), 'value'));
        foreach (AccessibilityComplianceStatusV1::cases() as $status) {
            $result = new AccessibilityComplianceResultV1($status, self::observedAt());
            self::assertSame($status, $result->status);
            self::assertSame('2026-08-08T10:00:00.123456Z', $result->observedAt);
            self::assertCount(2, get_object_vars($result));
        }
    }

    public function test_user_experience_catalogue_and_result_are_closed(): void
    {
        self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_column(UserExperienceStatusV1::cases(), 'value'));
        foreach (UserExperienceStatusV1::cases() as $status) {
            $result = new UserExperienceResultV1($status, self::observedAt());
            self::assertSame($status, $result->status);
            self::assertSame('2026-08-08T10:00:00.123456Z', $result->observedAt);
            self::assertCount(2, get_object_vars($result));
        }
    }

    public function test_end_to_end_readiness_catalogue_and_result_are_closed(): void
    {
        self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_column(EndToEndReadinessStatusV1::cases(), 'value'));
        foreach (EndToEndReadinessStatusV1::cases() as $status) {
            $result = new EndToEndReadinessResultV1($status, self::observedAt());
            self::assertSame($status, $result->status);
            self::assertSame('2026-08-08T10:00:00.123456Z', $result->observedAt);
            self::assertCount(2, get_object_vars($result));
        }
    }

    public function test_performance_readiness_catalogue_and_result_are_closed(): void
    {
        self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_column(PerformanceReadinessStatusV1::cases(), 'value'));
        foreach (PerformanceReadinessStatusV1::cases() as $status) {
            $result = new PerformanceReadinessResultV1($status, self::observedAt());
            self::assertSame($status, $result->status);
            self::assertSame('2026-08-08T10:00:00.123456Z', $result->observedAt);
            self::assertCount(2, get_object_vars($result));
        }
    }

    public function test_user_acceptance_catalogue_and_result_are_closed(): void
    {
        self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_column(UserAcceptanceStatusV1::cases(), 'value'));
        foreach (UserAcceptanceStatusV1::cases() as $status) {
            $result = new UserAcceptanceResultV1($status, self::observedAt());
            self::assertSame($status, $result->status);
            self::assertSame('2026-08-08T10:00:00.123456Z', $result->observedAt);
            self::assertCount(2, get_object_vars($result));
        }
    }

    public function test_release_candidate_catalogue_and_result_are_closed(): void
    {
        self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_column(ReleaseCandidateStatusV1::cases(), 'value'));
        foreach (ReleaseCandidateStatusV1::cases() as $status) {
            $result = new ReleaseCandidateResultV1($status, self::observedAt());
            self::assertSame($status, $result->status);
            self::assertSame('2026-08-08T10:00:00.123456Z', $result->observedAt);
            self::assertCount(2, get_object_vars($result));
        }
    }

    private static function observedAt(): ExperienceAcceptanceObservedAt
    {
        return new ExperienceAcceptanceObservedAt(new DateTimeImmutable('2026-08-08T12:00:00.123456+02:00'));
    }
}
