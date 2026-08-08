<?php

namespace Tests\Unit\ReliabilityOperations\Outbox;

use Appart\Modules\ReliabilityOperations\Application\Delivery\AlertingDeliveryPayload;
use Appart\Modules\ReliabilityOperations\Application\Delivery\AlertingDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\AlertingDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ContinuityDeliveryPayload;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ContinuityDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ContinuityDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\MaintenanceOperationsDeliveryPayload;
use Appart\Modules\ReliabilityOperations\Application\Delivery\MaintenanceOperationsDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\MaintenanceOperationsDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryPayload;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ServiceHealthDeliveryPayload;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ServiceHealthDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ServiceHealthDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventType;
use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventType;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxClaimResult;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxMessage;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxMessageStatus;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxPolicy;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxRetryResult;
use Appart\Modules\ReliabilityOperations\Infrastructure\Outbox\ReliabilityOperationsOutboxMapper;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReliabilityOperationsOutboxTest extends TestCase
{
    #[DataProvider('deliveries')]
    public function test_seven_delivery_types_have_deterministic_identity_checksum_and_minimal_payload(object $delivery): void
    {
        $mapper = new ReliabilityOperationsOutboxMapper;
        $first = $mapper->fromDelivery($delivery, self::createdAt());
        $second = $mapper->fromDelivery($delivery, self::createdAt());
        self::assertSame($first->messageId->value, $second->messageId->value);
        self::assertSame($first->eventId->value, $second->eventId->value);
        self::assertSame($first->checksum, $second->checksum);
        self::assertSame(ReliabilityOperationsOutboxMessage::OWNER, 'ReliabilityOperations');
        self::assertSame(['type', 'status', 'observedAt'], array_keys($first->payload()));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first->checksum);
    }

    public function test_same_identity_with_different_content_is_divergent_by_checksum(): void
    {
        $mapper = new ReliabilityOperationsOutboxMapper;
        $available = $mapper->fromDelivery(new ObservabilityDeliveryV1(ObservabilityEventType::Observed, new ObservabilityDeliveryPayload(ObservabilityDeliveryStatus::Available, self::observedAt())), self::createdAt());
        $missing = $mapper->fromDelivery(new ObservabilityDeliveryV1(ObservabilityEventType::Observed, new ObservabilityDeliveryPayload(ObservabilityDeliveryStatus::Missing, self::observedAt())), self::createdAt());
        self::assertSame($available->eventId->value, $missing->eventId->value);
        self::assertSame($available->messageId->value, $missing->messageId->value);
        self::assertNotSame($available->checksum, $missing->checksum);
    }

    public function test_retry_catalogue_is_closed_and_attempts_are_bounded_to_ten(): void
    {
        self::assertSame(['claimed', 'already_claimed', 'already_completed', 'attempts_exhausted', 'missing', 'corrupted', 'dependency_unavailable'], array_column(ReliabilityOperationsOutboxClaimResult::cases(), 'value'));
        $policy = new ReliabilityOperationsOutboxPolicy;
        self::assertTrue($policy->acceptsAttempts(10));
        self::assertFalse($policy->acceptsAttempts(11));
        self::assertSame(['retry_scheduled', 'attempts_exhausted', 'already_completed', 'missing', 'corrupted', 'dependency_unavailable'], array_column(ReliabilityOperationsOutboxRetryResult::cases(), 'value'));
        $message = (new ReliabilityOperationsOutboxMapper)->fromDelivery(self::deliveries()->current()[0], self::createdAt());
        $this->expectException(InvalidArgumentException::class);
        new ReliabilityOperationsOutboxMessage($message->messageId, $message->eventId, $message->type, $message->deliveryStatus, $message->observedAt, $message->checksum, $message->createdAt, $message->availableAt, 11, ReliabilityOperationsOutboxMessageStatus::Claimed);
    }

    /** @return \Generator<string, array{object}> */
    public static function deliveries(): \Generator
    {
        yield 'secret inventory' => [new ObservabilityDeliveryV1(ObservabilityEventType::Observed, new ObservabilityDeliveryPayload(ObservabilityDeliveryStatus::Available, self::observedAt()))];
        yield 'security audit' => [new ServiceHealthDeliveryV1(ServiceHealthEventType::Observed, new ServiceHealthDeliveryPayload(ServiceHealthDeliveryStatus::Healthy, self::observedAt()))];
        yield 'incident' => [new AlertingDeliveryV1(AlertingEventType::Observed, new AlertingDeliveryPayload(AlertingDeliveryStatus::Ready, self::observedAt()))];
        yield 'privacy policy' => [new MaintenanceOperationsDeliveryV1(MaintenanceOperationsEventType::Observed, new MaintenanceOperationsDeliveryPayload(MaintenanceOperationsDeliveryStatus::Ready, self::observedAt()))];
        yield 'compliance control' => [new ContinuityDeliveryV1(ContinuityEventType::Observed, new ContinuityDeliveryPayload(ContinuityDeliveryStatus::Ready, self::observedAt()))];
    }

    private static function observedAt(): string
    {
        return '2026-08-06T10:00:00.123456Z';
    }

    private static function createdAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-06T10:00:01.123456Z');
    }
}
