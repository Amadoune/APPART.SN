<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

use Appart\Modules\SecurityCompliance\Application\Delivery\ComplianceControlDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\IncidentDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\PrivacyPolicyDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecurityAuditDeliveryV1;
use DateTimeImmutable;

interface SecurityComplianceOutboxWriter
{
    public function append(SecretInventoryDeliveryV1|SecurityAuditDeliveryV1|IncidentDeliveryV1|PrivacyPolicyDeliveryV1|ComplianceControlDeliveryV1 $delivery, DateTimeImmutable $createdAt): SecurityComplianceOutboxAppendResult;
}
