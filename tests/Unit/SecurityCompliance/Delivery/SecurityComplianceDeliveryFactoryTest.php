<?php

namespace Tests\Unit\SecurityCompliance\Delivery;

use Appart\Modules\SecurityCompliance\Application\Delivery\ComplianceControlDeliveryFactory;
use Appart\Modules\SecurityCompliance\Application\Delivery\ComplianceControlDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\IncidentDeliveryFactory;
use Appart\Modules\SecurityCompliance\Application\Delivery\IncidentDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\PrivacyPolicyDeliveryFactory;
use Appart\Modules\SecurityCompliance\Application\Delivery\PrivacyPolicyDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryFactory;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecurityAuditDeliveryFactory;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecurityAuditDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventPayload;
use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventType;
use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventV1;
use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventPayload;
use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventType;
use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventV1;
use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventPayload;
use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventType;
use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventV1;
use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventPayload;
use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventType;
use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventV1;
use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventPayload;
use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventType;
use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SecurityComplianceDeliveryFactoryTest extends TestCase
{
    #[DataProvider('secretInventoryStatuses')]
    public function test_secret_inventory_event_produces_exactly_one_delivery(SecretInventoryEventStatus $status): void
    {
        $event = new SecretInventoryEventV1(SecretInventoryEventType::Observed, new SecretInventoryEventPayload($status, self::observedAt()));
        $result = (new SecretInventoryDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(SecretInventoryDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('securityAuditStatuses')]
    public function test_security_audit_event_produces_exactly_one_delivery(SecurityAuditEventStatus $status): void
    {
        $event = new SecurityAuditEventV1(SecurityAuditEventType::Observed, new SecurityAuditEventPayload($status, self::observedAt()));
        $result = (new SecurityAuditDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(SecurityAuditDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('incidentStatuses')]
    public function test_incident_event_produces_exactly_one_delivery(IncidentEventStatus $status): void
    {
        $event = new IncidentEventV1(IncidentEventType::Observed, new IncidentEventPayload($status, self::observedAt()));
        $result = (new IncidentDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(IncidentDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('privacyPolicyStatuses')]
    public function test_privacy_policy_event_produces_exactly_one_delivery(PrivacyPolicyEventStatus $status): void
    {
        $event = new PrivacyPolicyEventV1(PrivacyPolicyEventType::Observed, new PrivacyPolicyEventPayload($status, self::observedAt()));
        $result = (new PrivacyPolicyDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(PrivacyPolicyDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    #[DataProvider('complianceControlStatuses')]
    public function test_compliance_control_event_produces_exactly_one_delivery(ComplianceControlEventStatus $status): void
    {
        $event = new ComplianceControlEventV1(ComplianceControlEventType::Observed, new ComplianceControlEventPayload($status, self::observedAt()));
        $result = (new ComplianceControlDeliveryFactory)->create($event);
        self::assertSame($event->type, $result->delivery->type);
        self::assertSame(ComplianceControlDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['status' => $status->value, 'observedAt' => self::observedAt()], $result->delivery->payload->canonical());
        self::assertCount(2, get_object_vars($result->delivery->payload));
    }

    /** @return iterable<string, array{SecretInventoryEventStatus}> */
    public static function secretInventoryStatuses(): iterable
    {
        foreach (SecretInventoryEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{SecurityAuditEventStatus}> */
    public static function securityAuditStatuses(): iterable
    {
        foreach (SecurityAuditEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{IncidentEventStatus}> */
    public static function incidentStatuses(): iterable
    {
        foreach (IncidentEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{PrivacyPolicyEventStatus}> */
    public static function privacyPolicyStatuses(): iterable
    {
        foreach (PrivacyPolicyEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    /** @return iterable<string, array{ComplianceControlEventStatus}> */
    public static function complianceControlStatuses(): iterable
    {
        foreach (ComplianceControlEventStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    private static function observedAt(): string
    {
        return '2026-08-06T12:00:00.123456Z';
    }
}
