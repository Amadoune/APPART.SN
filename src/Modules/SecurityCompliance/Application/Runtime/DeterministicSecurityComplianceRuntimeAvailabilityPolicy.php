<?php

namespace Appart\Modules\SecurityCompliance\Application\Runtime;

use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicSecurityComplianceRuntimeAvailabilityPolicy implements SecurityComplianceRuntimeAvailabilityPolicy
{
    private const PROBE_SUBJECT = 'runtime/security-compliance-owner-source';

    public function __construct(private SecurityComplianceOwnerSource $source) {}

    public function inspect(): SecurityComplianceRuntimeAvailability
    {
        try {
            $subject = new SecurityComplianceSubjectKey(self::PROBE_SUBJECT);
            $observedAt = new SecurityComplianceObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z'));
            $secretInventory = $this->source->readSecretInventory($subject, $observedAt);
            $securityAudit = $this->source->readSecurityAudit($subject, $observedAt);
            $incident = $this->source->readIncident($subject, $observedAt);
            $privacyPolicy = $this->source->readPrivacyPolicy($subject, $observedAt);
            $complianceControl = $this->source->readComplianceControl($subject, $observedAt);

            if ($secretInventory->status === SecretInventoryStatusV1::DependencyUnavailable
                || $securityAudit->status === SecurityAuditStatusV1::DependencyUnavailable
                || $incident->status === IncidentStatusV1::DependencyUnavailable
                || $privacyPolicy->status === PrivacyPolicyStatusV1::DependencyUnavailable
                || $complianceControl->status === ComplianceControlStatusV1::DependencyUnavailable) {
                return SecurityComplianceRuntimeAvailability::DependencyUnavailable;
            }

            if ($secretInventory->status === SecretInventoryStatusV1::Corrupted
                || $securityAudit->status === SecurityAuditStatusV1::Corrupted
                || $incident->status === IncidentStatusV1::Corrupted
                || $privacyPolicy->status === PrivacyPolicyStatusV1::Corrupted
                || $complianceControl->status === ComplianceControlStatusV1::Corrupted) {
                return SecurityComplianceRuntimeAvailability::Corrupted;
            }

            return SecurityComplianceRuntimeAvailability::Available;
        } catch (Throwable) {
            return SecurityComplianceRuntimeAvailability::DependencyUnavailable;
        }
    }
}
