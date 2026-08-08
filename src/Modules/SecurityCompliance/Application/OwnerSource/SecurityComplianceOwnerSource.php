<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerSource;

use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

interface SecurityComplianceOwnerSource
{
    public function appendSecretInventory(SecretInventoryRevisionState $revision): SecretInventoryWriteResult;

    public function appendSecurityAudit(SecurityAuditRevisionState $revision): SecurityAuditWriteResult;

    public function appendIncident(IncidentRevisionState $revision): IncidentWriteResult;

    public function appendPrivacyPolicy(PrivacyPolicyRevisionState $revision): PrivacyPolicyWriteResult;

    public function appendComplianceControl(ComplianceControlRevisionState $revision): ComplianceControlWriteResult;

    public function readSecretInventory(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecretInventoryReadResult;

    public function readSecurityAudit(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecurityAuditReadResult;

    public function readIncident(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): IncidentReadResult;

    public function readPrivacyPolicy(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): PrivacyPolicyReadResult;

    public function readComplianceControl(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): ComplianceControlReadResult;
}
