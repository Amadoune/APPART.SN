<?php

namespace Tests\Unit\SecurityCompliance\Outbox;

use Appart\Modules\SecurityCompliance\Application\Delivery\ComplianceControlDeliveryPayload;
use Appart\Modules\SecurityCompliance\Application\Delivery\ComplianceControlDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\ComplianceControlDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\IncidentDeliveryPayload;
use Appart\Modules\SecurityCompliance\Application\Delivery\IncidentDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\IncidentDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\PrivacyPolicyDeliveryPayload;
use Appart\Modules\SecurityCompliance\Application\Delivery\PrivacyPolicyDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\PrivacyPolicyDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryPayload;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecurityAuditDeliveryPayload;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecurityAuditDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecurityAuditDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventType;
use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventType;
use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventType;
use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventType;
use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventType;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxClaimResult;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxMessage;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxMessageStatus;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxRetryResult;
use Appart\Modules\SecurityCompliance\Infrastructure\Outbox\SecurityComplianceOutboxMapper;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SecurityComplianceOutboxTest extends TestCase
{
    #[DataProvider('deliveries')]
    public function test_five_delivery_types_have_deterministic_identity_checksum_and_minimal_payload(object $delivery): void
    {
        $mapper = new SecurityComplianceOutboxMapper;
        $first = $mapper->fromDelivery($delivery, self::createdAt());
        $second = $mapper->fromDelivery($delivery, self::createdAt());
        self::assertSame($first->messageId->value, $second->messageId->value);
        self::assertSame($first->eventId->value, $second->eventId->value);
        self::assertSame($first->checksum, $second->checksum);
        self::assertSame(SecurityComplianceOutboxMessage::OWNER, 'SecurityCompliance');
        self::assertSame(['type', 'status', 'observedAt'], array_keys($first->payload()));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first->checksum);
    }

    public function test_same_identity_with_different_content_is_divergent_by_checksum(): void
    {
        $mapper = new SecurityComplianceOutboxMapper;
        $available = $mapper->fromDelivery(new SecretInventoryDeliveryV1(SecretInventoryEventType::Observed, new SecretInventoryDeliveryPayload(SecretInventoryDeliveryStatus::Available, self::observedAt())), self::createdAt());
        $missing = $mapper->fromDelivery(new SecretInventoryDeliveryV1(SecretInventoryEventType::Observed, new SecretInventoryDeliveryPayload(SecretInventoryDeliveryStatus::Missing, self::observedAt())), self::createdAt());
        self::assertSame($available->eventId->value, $missing->eventId->value);
        self::assertSame($available->messageId->value, $missing->messageId->value);
        self::assertNotSame($available->checksum, $missing->checksum);
    }

    public function test_retry_catalogue_is_closed_and_attempts_are_bounded_to_ten(): void
    {
        self::assertSame(['claimed', 'already_claimed', 'already_completed', 'attempts_exhausted', 'missing', 'corrupted', 'dependency_unavailable'], array_column(SecurityComplianceOutboxClaimResult::cases(), 'value'));
        self::assertSame(['retry_scheduled', 'attempts_exhausted', 'already_completed', 'missing', 'corrupted', 'dependency_unavailable'], array_column(SecurityComplianceOutboxRetryResult::cases(), 'value'));
        $message = (new SecurityComplianceOutboxMapper)->fromDelivery(self::deliveries()->current()[0], self::createdAt());
        $this->expectException(InvalidArgumentException::class);
        new SecurityComplianceOutboxMessage($message->messageId, $message->eventId, $message->type, $message->deliveryStatus, $message->observedAt, $message->checksum, $message->createdAt, $message->availableAt, 11, SecurityComplianceOutboxMessageStatus::Claimed);
    }

    /** @return \Generator<string, array{object}> */
    public static function deliveries(): \Generator
    {
        yield 'secret inventory' => [new SecretInventoryDeliveryV1(SecretInventoryEventType::Observed, new SecretInventoryDeliveryPayload(SecretInventoryDeliveryStatus::Available, self::observedAt()))];
        yield 'security audit' => [new SecurityAuditDeliveryV1(SecurityAuditEventType::Observed, new SecurityAuditDeliveryPayload(SecurityAuditDeliveryStatus::Available, self::observedAt()))];
        yield 'incident' => [new IncidentDeliveryV1(IncidentEventType::Observed, new IncidentDeliveryPayload(IncidentDeliveryStatus::Available, self::observedAt()))];
        yield 'privacy policy' => [new PrivacyPolicyDeliveryV1(PrivacyPolicyEventType::Observed, new PrivacyPolicyDeliveryPayload(PrivacyPolicyDeliveryStatus::Available, self::observedAt()))];
        yield 'compliance control' => [new ComplianceControlDeliveryV1(ComplianceControlEventType::Observed, new ComplianceControlDeliveryPayload(ComplianceControlDeliveryStatus::Available, self::observedAt()))];
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
