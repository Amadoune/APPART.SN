<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

use DateTimeImmutable;

interface SecurityComplianceOutboxStore extends SecurityComplianceOutboxReader, SecurityComplianceOutboxWriter
{
    public function claim(SecurityComplianceOutboxMessageId $messageId, DateTimeImmutable $claimedAt): SecurityComplianceOutboxClaimResult;

    public function retry(SecurityComplianceOutboxMessageId $messageId, DateTimeImmutable $availableAt): SecurityComplianceOutboxRetryResult;
}
