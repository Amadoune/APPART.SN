<?php

namespace App\Application\ModerationOperationalAudit\Contract;

use App\Application\ModerationOperationalAudit\ModerationOperationalAuditDelivery;
use DateTimeImmutable;

interface ModerationOperationalAuditDeliveryReaderV1
{
    public function claimNext(string $owner, DateTimeImmutable $now): ?ModerationOperationalAuditDelivery;

    public function retry(
        ModerationOperationalAuditDelivery $delivery,
        DateTimeImmutable $availableAt,
        string $errorCode,
    ): bool;

    public function markDelivered(
        ModerationOperationalAuditDelivery $delivery,
        DateTimeImmutable $at,
    ): bool;

    public function quarantine(
        ModerationOperationalAuditDelivery $delivery,
        string $errorCode,
    ): bool;
}
