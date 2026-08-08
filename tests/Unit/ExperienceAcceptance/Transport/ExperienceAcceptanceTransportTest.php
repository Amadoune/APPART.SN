<?php

namespace Tests\Unit\ExperienceAcceptance\Transport;

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
use Appart\Modules\ExperienceAcceptance\Application\Transport\DeterministicExperienceAcceptanceTransport;
use Appart\Modules\ExperienceAcceptance\Application\Transport\ExperienceAcceptanceTransportException;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Outbox\ExperienceAcceptanceOutboxMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceTransportTest extends TestCase
{
    #[DataProvider('deliveries')]
    public function test_outbox_message_round_trip_is_deterministic_and_bijective(object $delivery): void
    {
        $message = (new ExperienceAcceptanceOutboxMapper)->fromDelivery($delivery, new DateTimeImmutable('2026-08-08T10:00:01.123456Z'));
        $transport = new DeterministicExperienceAcceptanceTransport;
        $first = $transport->serialize($message);
        $second = $transport->serialize($message);
        self::assertSame($first, $second);
        $envelope = $transport->deserialize($first);
        self::assertSame($message->messageId->value, $envelope->messageId->value);
        self::assertSame($message->eventId->value, $envelope->eventId->value);
        self::assertSame($message->type, $envelope->type);
        self::assertSame($message->deliveryStatus, $envelope->status);
        self::assertSame($message->observedAt, $envelope->observedAt);
        self::assertSame($message->checksum, $envelope->checksum);
        self::assertSame($first, json_encode($envelope->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    public function test_unknown_fields_are_rejected(): void
    {
        $this->expectException(ExperienceAcceptanceTransportException::class);
        (new DeterministicExperienceAcceptanceTransport)->deserialize('{"messageId":"x","unexpected":true}');
    }

    public function test_checksum_divergence_is_rejected(): void
    {
        $message = (new ExperienceAcceptanceOutboxMapper)->fromDelivery(self::deliveries()->current()[0], new DateTimeImmutable('2026-08-08T10:00:01.123456Z'));
        $serialized = (new DeterministicExperienceAcceptanceTransport)->serialize($message);
        $data = json_decode($serialized, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);
        $data['status'] = 'missing';
        $this->expectException(ExperienceAcceptanceTransportException::class);
        (new DeterministicExperienceAcceptanceTransport)->deserialize(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    /** @return \Generator<string, array{object}> */
    public static function deliveries(): \Generator
    {
        yield 'ResponsiveCompliance' => [new ResponsiveComplianceDeliveryV1(ResponsiveComplianceEventType::Observed, new ResponsiveComplianceDeliveryPayload(ResponsiveComplianceDeliveryStatus::Available, '2026-08-08T10:00:00.123456Z'))];
        yield 'AccessibilityCompliance' => [new AccessibilityComplianceDeliveryV1(AccessibilityComplianceEventType::Observed, new AccessibilityComplianceDeliveryPayload(AccessibilityComplianceDeliveryStatus::Available, '2026-08-08T10:00:00.123456Z'))];
        yield 'UserExperience' => [new UserExperienceDeliveryV1(UserExperienceEventType::Observed, new UserExperienceDeliveryPayload(UserExperienceDeliveryStatus::Available, '2026-08-08T10:00:00.123456Z'))];
        yield 'EndToEndReadiness' => [new EndToEndReadinessDeliveryV1(EndToEndReadinessEventType::Observed, new EndToEndReadinessDeliveryPayload(EndToEndReadinessDeliveryStatus::Available, '2026-08-08T10:00:00.123456Z'))];
        yield 'PerformanceReadiness' => [new PerformanceReadinessDeliveryV1(PerformanceReadinessEventType::Observed, new PerformanceReadinessDeliveryPayload(PerformanceReadinessDeliveryStatus::Available, '2026-08-08T10:00:00.123456Z'))];
        yield 'UserAcceptance' => [new UserAcceptanceDeliveryV1(UserAcceptanceEventType::Observed, new UserAcceptanceDeliveryPayload(UserAcceptanceDeliveryStatus::Available, '2026-08-08T10:00:00.123456Z'))];
        yield 'ReleaseCandidate' => [new ReleaseCandidateDeliveryV1(ReleaseCandidateEventType::Observed, new ReleaseCandidateDeliveryPayload(ReleaseCandidateDeliveryStatus::Available, '2026-08-08T10:00:00.123456Z'))];
    }
}
