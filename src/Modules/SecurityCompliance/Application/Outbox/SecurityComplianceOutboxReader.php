<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

use DateTimeImmutable;

interface SecurityComplianceOutboxReader
{
    /** @return list<SecurityComplianceOutboxMessage> */
    public function eligible(DateTimeImmutable $availableAt, int $limit): array;
}
