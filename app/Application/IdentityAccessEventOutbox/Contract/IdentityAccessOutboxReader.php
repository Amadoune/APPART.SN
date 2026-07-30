<?php

namespace App\Application\IdentityAccessEventOutbox\Contract;

use App\Application\IdentityAccessEventOutbox\IdentityAccessOutboxDelivery;
use DateTimeImmutable;

interface IdentityAccessOutboxReader
{
    public function claimNext(string $owner, DateTimeImmutable $now): ?IdentityAccessOutboxDelivery;

    public function markDelivered(IdentityAccessOutboxDelivery $delivery, DateTimeImmutable $at): bool;

    public function scheduleRetry(IdentityAccessOutboxDelivery $delivery, DateTimeImmutable $availableAt, string $errorCode): bool;

    public function quarantine(IdentityAccessOutboxDelivery $delivery, string $errorCode): bool;
}
