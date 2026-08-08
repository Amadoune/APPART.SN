<?php

namespace Tests\Unit\SecurityCompliance\OwnerReader;

use Appart\Modules\SecurityCompliance\Application\OwnerReader\ComplianceControlOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\IncidentOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\PrivacyPolicyOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\SecretInventoryOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\SecurityAuditOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\SecurityComplianceOwnerReaderPolicy;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SecurityComplianceOwnerReaderTest extends TestCase
{
    public function test_policy_reductions_are_exhaustive_mechanical_and_homonymous(): void
    {
        $policy = new SecurityComplianceOwnerReaderPolicy;
        foreach ([SecretInventoryReadResult::found(new SecretInventoryRevisionState($this->subject(), 1, SecretInventoryStatusV1::Available, $this->effectiveAt(), $this->recordedAt())), SecretInventoryReadResult::missing(), SecretInventoryReadResult::corrupted(), SecretInventoryReadResult::dependencyUnavailable()] as $result) {
            self::assertSame($result->status->value, $policy->secretInventory($result)->status->value);
        }
        foreach ([SecurityAuditReadResult::found(new SecurityAuditRevisionState($this->subject(), 1, SecurityAuditStatusV1::Available, $this->effectiveAt(), $this->recordedAt())), SecurityAuditReadResult::missing(), SecurityAuditReadResult::corrupted(), SecurityAuditReadResult::dependencyUnavailable()] as $result) {
            self::assertSame($result->status->value, $policy->securityAudit($result)->status->value);
        }
        foreach ([IncidentReadResult::found(new IncidentRevisionState($this->subject(), 1, IncidentStatusV1::Available, $this->effectiveAt(), $this->recordedAt())), IncidentReadResult::missing(), IncidentReadResult::corrupted(), IncidentReadResult::dependencyUnavailable()] as $result) {
            self::assertSame($result->status->value, $policy->incident($result)->status->value);
        }
        foreach ([PrivacyPolicyReadResult::found(new PrivacyPolicyRevisionState($this->subject(), 1, PrivacyPolicyStatusV1::Available, $this->effectiveAt(), $this->recordedAt())), PrivacyPolicyReadResult::missing(), PrivacyPolicyReadResult::corrupted(), PrivacyPolicyReadResult::dependencyUnavailable()] as $result) {
            self::assertSame($result->status->value, $policy->privacyPolicy($result)->status->value);
        }
        foreach ([ComplianceControlReadResult::found(new ComplianceControlRevisionState($this->subject(), 1, ComplianceControlStatusV1::Available, $this->effectiveAt(), $this->recordedAt())), ComplianceControlReadResult::missing(), ComplianceControlReadResult::corrupted(), ComplianceControlReadResult::dependencyUnavailable()] as $result) {
            self::assertSame($result->status->value, $policy->complianceControl($result)->status->value);
        }
    }

    public function test_five_public_readers_copy_only_status_and_observed_at(): void
    {
        $source = $this->createMock(SecurityComplianceOwnerSource::class);
        $source->method('readSecretInventory')->willReturn(SecretInventoryReadResult::missing());
        $source->method('readSecurityAudit')->willReturn(SecurityAuditReadResult::missing());
        $source->method('readIncident')->willReturn(IncidentReadResult::missing());
        $source->method('readPrivacyPolicy')->willReturn(PrivacyPolicyReadResult::missing());
        $source->method('readComplianceControl')->willReturn(ComplianceControlReadResult::missing());
        $policy = new SecurityComplianceOwnerReaderPolicy;
        $results = [
            (new SecretInventoryOwnerReader($source, $policy))->read($this->subject(), $this->observedAt()),
            (new SecurityAuditOwnerReader($source, $policy))->read($this->subject(), $this->observedAt()),
            (new IncidentOwnerReader($source, $policy))->read($this->subject(), $this->observedAt()),
            (new PrivacyPolicyOwnerReader($source, $policy))->read($this->subject(), $this->observedAt()),
            (new ComplianceControlOwnerReader($source, $policy))->read($this->subject(), $this->observedAt()),
        ];
        foreach ($results as $result) {
            self::assertSame('missing', $result->status->value);
            self::assertSame($this->observedAt()->canonical(), $result->observedAt);
            self::assertCount(2, get_object_vars($result));
        }
    }

    private function subject(): SecurityComplianceSubjectKey
    {
        return new SecurityComplianceSubjectKey('security-compliance:owner-reader-test');
    }

    private function observedAt(): SecurityComplianceObservedAt
    {
        return new SecurityComplianceObservedAt(new DateTimeImmutable('2026-08-04T12:00:00Z'));
    }

    private function effectiveAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-04T10:00:00Z');
    }

    private function recordedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-04T10:00:01Z');
    }
}
