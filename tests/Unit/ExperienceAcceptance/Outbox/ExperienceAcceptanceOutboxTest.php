<?php

namespace Tests\Unit\ExperienceAcceptance\Outbox;

use Appart\Modules\ExperienceAcceptance\Application\Delivery\AccessibilityComplianceDeliveryPayload;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\AccessibilityComplianceDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\AccessibilityComplianceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\EndToEndReadinessDeliveryPayload;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\EndToEndReadinessDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\EndToEndReadinessDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\PerformanceReadinessDeliveryPayload;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\PerformanceReadinessDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\PerformanceReadinessDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ReleaseCandidateDeliveryPayload;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ReleaseCandidateDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ReleaseCandidateDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryPayload;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserAcceptanceDeliveryPayload;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserAcceptanceDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserAcceptanceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserExperienceDeliveryPayload;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserExperienceDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserExperienceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxClaimResult;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessage;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageStatus;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxPolicy;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxRetryResult;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Outbox\ExperienceAcceptanceOutboxMapper;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceOutboxTest extends TestCase
{
    #[DataProvider('deliveries')]
    public function test_seven_delivery_types_have_deterministic_identity_checksum_and_minimal_payload(object $delivery): void
    {
        $mapper = new ExperienceAcceptanceOutboxMapper;
        $first = $mapper->fromDelivery($delivery, self::createdAt());
        $second = $mapper->fromDelivery($delivery, self::createdAt());
        self::assertSame($first->messageId->value, $second->messageId->value);
        self::assertSame($first->eventId->value, $second->eventId->value);
        self::assertSame($first->checksum, $second->checksum);
        self::assertSame('ExperienceAcceptance', ExperienceAcceptanceOutboxMessage::OWNER);
        self::assertSame(['type', 'status', 'observedAt'], array_keys($first->payload()));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first->checksum);
    }

    public function test_same_identity_with_different_content_is_divergent_by_checksum(): void
    {
        $mapper = new ExperienceAcceptanceOutboxMapper;
        $available = $mapper->fromDelivery(new ResponsiveComplianceDeliveryV1(ResponsiveComplianceEventType::Observed, new ResponsiveComplianceDeliveryPayload(ResponsiveComplianceDeliveryStatus::Available, self::observedAt())), self::createdAt());
        $missing = $mapper->fromDelivery(new ResponsiveComplianceDeliveryV1(ResponsiveComplianceEventType::Observed, new ResponsiveComplianceDeliveryPayload(ResponsiveComplianceDeliveryStatus::Missing, self::observedAt())), self::createdAt());
        self::assertSame($available->eventId->value, $missing->eventId->value);
        self::assertSame($available->messageId->value, $missing->messageId->value);
        self::assertNotSame($available->checksum, $missing->checksum);
    }

    public function test_retry_catalogue_is_closed_and_attempts_are_bounded_to_ten(): void
    {
        self::assertSame(['claimed', 'already_claimed', 'already_completed', 'attempts_exhausted', 'missing', 'corrupted', 'dependency_unavailable'], array_column(ExperienceAcceptanceOutboxClaimResult::cases(), 'value'));
        $policy = new ExperienceAcceptanceOutboxPolicy;
        self::assertTrue($policy->acceptsAttempts(10));
        self::assertFalse($policy->acceptsAttempts(11));
        self::assertSame(['retry_scheduled', 'attempts_exhausted', 'already_completed', 'missing', 'corrupted', 'dependency_unavailable'], array_column(ExperienceAcceptanceOutboxRetryResult::cases(), 'value'));
        $message = (new ExperienceAcceptanceOutboxMapper)->fromDelivery(self::deliveries()->current()[0], self::createdAt());
        $this->expectException(InvalidArgumentException::class);
        new ExperienceAcceptanceOutboxMessage($message->messageId, $message->eventId, $message->type, $message->deliveryStatus, $message->observedAt, $message->checksum, $message->createdAt, $message->availableAt, 11, ExperienceAcceptanceOutboxMessageStatus::Claimed);
    }

    /** @return \Generator<string, array{object}> */
    public static function deliveries(): \Generator
    {
        yield 'ResponsiveCompliance' => [new ResponsiveComplianceDeliveryV1(ResponsiveComplianceEventType::Observed, new ResponsiveComplianceDeliveryPayload(ResponsiveComplianceDeliveryStatus::Available, self::observedAt()))];
        yield 'AccessibilityCompliance' => [new AccessibilityComplianceDeliveryV1(AccessibilityComplianceEventType::Observed, new AccessibilityComplianceDeliveryPayload(AccessibilityComplianceDeliveryStatus::Available, self::observedAt()))];
        yield 'UserExperience' => [new UserExperienceDeliveryV1(UserExperienceEventType::Observed, new UserExperienceDeliveryPayload(UserExperienceDeliveryStatus::Available, self::observedAt()))];
        yield 'EndToEndReadiness' => [new EndToEndReadinessDeliveryV1(EndToEndReadinessEventType::Observed, new EndToEndReadinessDeliveryPayload(EndToEndReadinessDeliveryStatus::Available, self::observedAt()))];
        yield 'PerformanceReadiness' => [new PerformanceReadinessDeliveryV1(PerformanceReadinessEventType::Observed, new PerformanceReadinessDeliveryPayload(PerformanceReadinessDeliveryStatus::Available, self::observedAt()))];
        yield 'UserAcceptance' => [new UserAcceptanceDeliveryV1(UserAcceptanceEventType::Observed, new UserAcceptanceDeliveryPayload(UserAcceptanceDeliveryStatus::Available, self::observedAt()))];
        yield 'ReleaseCandidate' => [new ReleaseCandidateDeliveryV1(ReleaseCandidateEventType::Observed, new ReleaseCandidateDeliveryPayload(ReleaseCandidateDeliveryStatus::Available, self::observedAt()))];
    }

    private static function observedAt(): string
    {
        return '2026-08-08T10:00:00.123456Z';
    }

    private static function createdAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-08T10:00:01.123456Z');
    }
}
