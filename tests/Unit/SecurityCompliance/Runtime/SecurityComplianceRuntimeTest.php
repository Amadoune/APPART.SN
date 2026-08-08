<?php

namespace Tests\Unit\SecurityCompliance\Runtime;

use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\Runtime\DeterministicSecurityComplianceRuntime;
use Appart\Modules\SecurityCompliance\Application\Runtime\DeterministicSecurityComplianceRuntimeAvailabilityPolicy;
use Appart\Modules\SecurityCompliance\Application\Runtime\SecurityComplianceRuntimeAvailability;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SecurityComplianceRuntimeTest extends TestCase
{
    public function test_missing_on_all_streams_is_technically_available(): void
    {
        $source = $this->sourceWith(SecretInventoryReadResult::missing(), SecurityAuditReadResult::missing(), IncidentReadResult::missing(), PrivacyPolicyReadResult::missing(), ComplianceControlReadResult::missing());
        self::assertSame(SecurityComplianceRuntimeAvailability::Available, (new DeterministicSecurityComplianceRuntimeAvailabilityPolicy($source))->inspect());
    }

    public function test_corruption_and_dependency_failure_are_reduced_deterministically(): void
    {
        $corrupted = $this->sourceWith(SecretInventoryReadResult::corrupted(), SecurityAuditReadResult::missing(), IncidentReadResult::missing(), PrivacyPolicyReadResult::missing(), ComplianceControlReadResult::missing());
        $dependency = $this->sourceWith(SecretInventoryReadResult::corrupted(), SecurityAuditReadResult::dependencyUnavailable(), IncidentReadResult::missing(), PrivacyPolicyReadResult::missing(), ComplianceControlReadResult::missing());
        self::assertSame(SecurityComplianceRuntimeAvailability::Corrupted, (new DeterministicSecurityComplianceRuntimeAvailabilityPolicy($corrupted))->inspect());
        self::assertSame(SecurityComplianceRuntimeAvailability::DependencyUnavailable, (new DeterministicSecurityComplianceRuntimeAvailabilityPolicy($dependency))->inspect());
    }

    public function test_exception_is_dependency_unavailable_and_diagnostics_are_minimal(): void
    {
        $source = $this->createMock(SecurityComplianceOwnerSource::class);
        $source->method('readSecretInventory')->willThrowException(new RuntimeException('technical'));
        $runtime = new DeterministicSecurityComplianceRuntime(new DeterministicSecurityComplianceRuntimeAvailabilityPolicy($source));
        self::assertSame(SecurityComplianceRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        self::assertSame([
            'runtimeId' => 'security-compliance.owner-source',
            'version' => 'security-compliance-runtime-v1',
            'availability' => SecurityComplianceRuntimeAvailability::DependencyUnavailable,
        ], get_object_vars($runtime->diagnostics()));
    }

    private function sourceWith(SecretInventoryReadResult $secretInventory, SecurityAuditReadResult $securityAudit, IncidentReadResult $incident, PrivacyPolicyReadResult $privacyPolicy, ComplianceControlReadResult $complianceControl): SecurityComplianceOwnerSource
    {
        $source = $this->createMock(SecurityComplianceOwnerSource::class);
        $source->method('readSecretInventory')->willReturn($secretInventory);
        $source->method('readSecurityAudit')->willReturn($securityAudit);
        $source->method('readIncident')->willReturn($incident);
        $source->method('readPrivacyPolicy')->willReturn($privacyPolicy);
        $source->method('readComplianceControl')->willReturn($complianceControl);

        return $source;
    }
}
