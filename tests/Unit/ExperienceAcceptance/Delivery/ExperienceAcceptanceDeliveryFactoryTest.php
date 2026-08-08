<?php

namespace Tests\Unit\ExperienceAcceptance\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Delivery\AccessibilityComplianceDeliveryFactory;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\AccessibilityComplianceDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\EndToEndReadinessDeliveryFactory;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\EndToEndReadinessDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\PerformanceReadinessDeliveryFactory;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\PerformanceReadinessDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ReleaseCandidateDeliveryFactory;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ReleaseCandidateDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryFactory;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserAcceptanceDeliveryFactory;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserAcceptanceDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserExperienceDeliveryFactory;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserExperienceDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventPayload;
use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventV1;
use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventPayload;
use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventV1;
use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventPayload;
use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventV1;
use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventPayload;
use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventV1;
use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventPayload;
use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventV1;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventPayload;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventV1;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventPayload;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventStatus;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventV1;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceDeliveryFactoryTest extends TestCase
{
    public function test_responsive_compliance_events_produce_exactly_one_delivery(): void
    {
        foreach (ResponsiveComplianceEventStatus::cases() as $status) {
            $event = new ResponsiveComplianceEventV1(ResponsiveComplianceEventType::Observed, new ResponsiveComplianceEventPayload($status, self::observedAt()));
            $result = (new ResponsiveComplianceDeliveryFactory)->create($event);
            self::assertSame($event->type, $result->delivery->type);
            self::assertSame(ResponsiveComplianceDeliveryStatus::from($status->value), $result->status());
            self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
            self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
            self::assertCount(2, get_object_vars($result->delivery->payload));
        }
    }

    public function test_accessibility_compliance_events_produce_exactly_one_delivery(): void
    {
        foreach (AccessibilityComplianceEventStatus::cases() as $status) {
            $event = new AccessibilityComplianceEventV1(AccessibilityComplianceEventType::Observed, new AccessibilityComplianceEventPayload($status, self::observedAt()));
            $result = (new AccessibilityComplianceDeliveryFactory)->create($event);
            self::assertSame($event->type, $result->delivery->type);
            self::assertSame(AccessibilityComplianceDeliveryStatus::from($status->value), $result->status());
            self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
            self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
            self::assertCount(2, get_object_vars($result->delivery->payload));
        }
    }

    public function test_user_experience_events_produce_exactly_one_delivery(): void
    {
        foreach (UserExperienceEventStatus::cases() as $status) {
            $event = new UserExperienceEventV1(UserExperienceEventType::Observed, new UserExperienceEventPayload($status, self::observedAt()));
            $result = (new UserExperienceDeliveryFactory)->create($event);
            self::assertSame($event->type, $result->delivery->type);
            self::assertSame(UserExperienceDeliveryStatus::from($status->value), $result->status());
            self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
            self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
            self::assertCount(2, get_object_vars($result->delivery->payload));
        }
    }

    public function test_end_to_end_readiness_events_produce_exactly_one_delivery(): void
    {
        foreach (EndToEndReadinessEventStatus::cases() as $status) {
            $event = new EndToEndReadinessEventV1(EndToEndReadinessEventType::Observed, new EndToEndReadinessEventPayload($status, self::observedAt()));
            $result = (new EndToEndReadinessDeliveryFactory)->create($event);
            self::assertSame($event->type, $result->delivery->type);
            self::assertSame(EndToEndReadinessDeliveryStatus::from($status->value), $result->status());
            self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
            self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
            self::assertCount(2, get_object_vars($result->delivery->payload));
        }
    }

    public function test_performance_readiness_events_produce_exactly_one_delivery(): void
    {
        foreach (PerformanceReadinessEventStatus::cases() as $status) {
            $event = new PerformanceReadinessEventV1(PerformanceReadinessEventType::Observed, new PerformanceReadinessEventPayload($status, self::observedAt()));
            $result = (new PerformanceReadinessDeliveryFactory)->create($event);
            self::assertSame($event->type, $result->delivery->type);
            self::assertSame(PerformanceReadinessDeliveryStatus::from($status->value), $result->status());
            self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
            self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
            self::assertCount(2, get_object_vars($result->delivery->payload));
        }
    }

    public function test_user_acceptance_events_produce_exactly_one_delivery(): void
    {
        foreach (UserAcceptanceEventStatus::cases() as $status) {
            $event = new UserAcceptanceEventV1(UserAcceptanceEventType::Observed, new UserAcceptanceEventPayload($status, self::observedAt()));
            $result = (new UserAcceptanceDeliveryFactory)->create($event);
            self::assertSame($event->type, $result->delivery->type);
            self::assertSame(UserAcceptanceDeliveryStatus::from($status->value), $result->status());
            self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
            self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
            self::assertCount(2, get_object_vars($result->delivery->payload));
        }
    }

    public function test_release_candidate_events_produce_exactly_one_delivery(): void
    {
        foreach (ReleaseCandidateEventStatus::cases() as $status) {
            $event = new ReleaseCandidateEventV1(ReleaseCandidateEventType::Observed, new ReleaseCandidateEventPayload($status, self::observedAt()));
            $result = (new ReleaseCandidateDeliveryFactory)->create($event);
            self::assertSame($event->type, $result->delivery->type);
            self::assertSame(ReleaseCandidateDeliveryStatus::from($status->value), $result->status());
            self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
            self::assertSame($event->payload->observedAt, $result->delivery->payload->observedAt);
            self::assertCount(2, get_object_vars($result->delivery->payload));
        }
    }

    private static function observedAt(): string
    {
        return '2026-08-08T12:00:01.654321Z';
    }
}
