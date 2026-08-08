<?php

namespace Tests\Unit\SecurityCompliance\Event;

use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventFactory;
use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventType;
use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventFactory;
use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventType;
use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventFactory;
use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventType;
use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventFactory;
use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventType;
use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventFactory;
use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventStatus;
use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventType;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\ComplianceControlReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\IncidentReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\PrivacyPolicyReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecretInventoryReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecurityAuditReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SecurityComplianceEventFactoryTest extends TestCase
{
    #[DataProvider('secretInventoryCases')]
    public function test_secret_inventory_result_produces_one_homonymous_event(SecretInventoryStatusV1 $public, SecretInventoryEventStatus $eventStatus): void
    {
        $reader = $this->createMock(SecretInventoryReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new SecretInventoryResultV1($public, self::resultObservedAt()));
        $event = (new SecretInventoryEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(SecretInventoryEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-06T12:00:01.654321Z'], $event->payload->canonical());
    }

    #[DataProvider('securityAuditCases')]
    public function test_security_audit_result_produces_one_homonymous_event(SecurityAuditStatusV1 $public, SecurityAuditEventStatus $eventStatus): void
    {
        $reader = $this->createMock(SecurityAuditReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new SecurityAuditResultV1($public, self::resultObservedAt()));
        $event = (new SecurityAuditEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(SecurityAuditEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-06T12:00:01.654321Z'], $event->payload->canonical());
    }

    #[DataProvider('incidentCases')]
    public function test_incident_result_produces_one_homonymous_event(IncidentStatusV1 $public, IncidentEventStatus $eventStatus): void
    {
        $reader = $this->createMock(IncidentReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new IncidentResultV1($public, self::resultObservedAt()));
        $event = (new IncidentEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(IncidentEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-06T12:00:01.654321Z'], $event->payload->canonical());
    }

    #[DataProvider('privacyPolicyCases')]
    public function test_privacy_policy_result_produces_one_homonymous_event(PrivacyPolicyStatusV1 $public, PrivacyPolicyEventStatus $eventStatus): void
    {
        $reader = $this->createMock(PrivacyPolicyReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new PrivacyPolicyResultV1($public, self::resultObservedAt()));
        $event = (new PrivacyPolicyEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(PrivacyPolicyEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-06T12:00:01.654321Z'], $event->payload->canonical());
    }

    #[DataProvider('complianceControlCases')]
    public function test_compliance_control_result_produces_one_homonymous_event(ComplianceControlStatusV1 $public, ComplianceControlEventStatus $eventStatus): void
    {
        $reader = $this->createMock(ComplianceControlReaderV1::class);
        $reader->expects(self::once())->method('read')->willReturn(new ComplianceControlResultV1($public, self::resultObservedAt()));
        $event = (new ComplianceControlEventFactory($reader))->create(self::subject(), self::requestedObservedAt());
        self::assertSame(ComplianceControlEventType::Observed, $event->type);
        self::assertSame(['status' => $eventStatus->value, 'observedAt' => '2026-08-06T12:00:01.654321Z'], $event->payload->canonical());
    }

    /** @return iterable<string, array{SecretInventoryStatusV1, SecretInventoryEventStatus}> */
    public static function secretInventoryCases(): iterable
    {
        foreach (SecretInventoryStatusV1::cases() as $status) {
            yield $status->value => [$status, SecretInventoryEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{SecurityAuditStatusV1, SecurityAuditEventStatus}> */
    public static function securityAuditCases(): iterable
    {
        foreach (SecurityAuditStatusV1::cases() as $status) {
            yield $status->value => [$status, SecurityAuditEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{IncidentStatusV1, IncidentEventStatus}> */
    public static function incidentCases(): iterable
    {
        foreach (IncidentStatusV1::cases() as $status) {
            yield $status->value => [$status, IncidentEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{PrivacyPolicyStatusV1, PrivacyPolicyEventStatus}> */
    public static function privacyPolicyCases(): iterable
    {
        foreach (PrivacyPolicyStatusV1::cases() as $status) {
            yield $status->value => [$status, PrivacyPolicyEventStatus::from($status->value)];
        }
    }

    /** @return iterable<string, array{ComplianceControlStatusV1, ComplianceControlEventStatus}> */
    public static function complianceControlCases(): iterable
    {
        foreach (ComplianceControlStatusV1::cases() as $status) {
            yield $status->value => [$status, ComplianceControlEventStatus::from($status->value)];
        }
    }

    private static function subject(): SecurityComplianceSubjectKey
    {
        return new SecurityComplianceSubjectKey('security-compliance:42');
    }

    private static function requestedObservedAt(): SecurityComplianceObservedAt
    {
        return new SecurityComplianceObservedAt(new DateTimeImmutable('2026-08-06T12:00:00.123456Z'));
    }

    private static function resultObservedAt(): SecurityComplianceObservedAt
    {
        return new SecurityComplianceObservedAt(new DateTimeImmutable('2026-08-06T12:00:01.654321Z'));
    }
}
