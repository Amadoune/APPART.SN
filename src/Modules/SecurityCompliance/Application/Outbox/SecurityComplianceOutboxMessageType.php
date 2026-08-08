<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

enum SecurityComplianceOutboxMessageType: string
{
    case SecretInventory = 'security-compliance.secret-inventory.observed.v1';
    case SecurityAudit = 'security-compliance.security-audit.observed.v1';
    case Incident = 'security-compliance.incident.observed.v1';
    case PrivacyPolicy = 'security-compliance.privacy-policy.observed.v1';
    case ComplianceControl = 'security-compliance.compliance-control.observed.v1';
}
