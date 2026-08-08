<?php

namespace Tests\Unit\ExperienceAcceptance\Event;

use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventFactory;
use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventFactory;
use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventFactory;
use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventFactory;
use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventFactory;
use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventFactory;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventFactory;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventType;
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
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceEventFactoryTest extends TestCase
{
    public function test_responsive_compliance_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (ResponsiveComplianceStatusV1::cases() as $status) {
            $reader = $this->createMock(ResponsiveComplianceReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new ResponsiveComplianceResultV1($status, self::resultObservedAt()));
            $event = (new ResponsiveComplianceEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(ResponsiveComplianceEventType::Observed, $event->type);
            self::assertSame(ResponsiveComplianceEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_accessibility_compliance_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (AccessibilityComplianceStatusV1::cases() as $status) {
            $reader = $this->createMock(AccessibilityComplianceReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new AccessibilityComplianceResultV1($status, self::resultObservedAt()));
            $event = (new AccessibilityComplianceEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(AccessibilityComplianceEventType::Observed, $event->type);
            self::assertSame(AccessibilityComplianceEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_user_experience_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (UserExperienceStatusV1::cases() as $status) {
            $reader = $this->createMock(UserExperienceReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new UserExperienceResultV1($status, self::resultObservedAt()));
            $event = (new UserExperienceEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(UserExperienceEventType::Observed, $event->type);
            self::assertSame(UserExperienceEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_end_to_end_readiness_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (EndToEndReadinessStatusV1::cases() as $status) {
            $reader = $this->createMock(EndToEndReadinessReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new EndToEndReadinessResultV1($status, self::resultObservedAt()));
            $event = (new EndToEndReadinessEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(EndToEndReadinessEventType::Observed, $event->type);
            self::assertSame(EndToEndReadinessEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_performance_readiness_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (PerformanceReadinessStatusV1::cases() as $status) {
            $reader = $this->createMock(PerformanceReadinessReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new PerformanceReadinessResultV1($status, self::resultObservedAt()));
            $event = (new PerformanceReadinessEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(PerformanceReadinessEventType::Observed, $event->type);
            self::assertSame(PerformanceReadinessEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_user_acceptance_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (UserAcceptanceStatusV1::cases() as $status) {
            $reader = $this->createMock(UserAcceptanceReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new UserAcceptanceResultV1($status, self::resultObservedAt()));
            $event = (new UserAcceptanceEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(UserAcceptanceEventType::Observed, $event->type);
            self::assertSame(UserAcceptanceEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    public function test_release_candidate_reductions_are_exhaustive_and_homonymous(): void
    {
        foreach (ReleaseCandidateStatusV1::cases() as $status) {
            $reader = $this->createMock(ReleaseCandidateReaderV1::class);
            $reader->expects(self::once())->method('read')->willReturn(new ReleaseCandidateResultV1($status, self::resultObservedAt()));
            $event = (new ReleaseCandidateEventFactory($reader))->create(self::requestedObservedAt());
            self::assertSame(ReleaseCandidateEventType::Observed, $event->type);
            self::assertSame(ReleaseCandidateEventStatus::from($status->value), $event->payload->status);
            self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-08T12:00:01.654321Z'], $event->payload->canonical());
        }
    }

    private static function requestedObservedAt(): ExperienceAcceptanceObservedAt
    {
        return new ExperienceAcceptanceObservedAt(new DateTimeImmutable('2026-08-08T12:00:00.123456Z'));
    }

    private static function resultObservedAt(): ExperienceAcceptanceObservedAt
    {
        return new ExperienceAcceptanceObservedAt(new DateTimeImmutable('2026-08-08T12:00:01.654321Z'));
    }
}
