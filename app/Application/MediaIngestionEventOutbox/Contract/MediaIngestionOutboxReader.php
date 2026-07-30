<?php

namespace App\Application\MediaIngestionEventOutbox\Contract;

use App\Application\MediaIngestionEventOutbox\MediaIngestionOutboxDelivery;
use DateTimeImmutable;

interface MediaIngestionOutboxReader
{
    public function claimNext(string $owner, DateTimeImmutable $now): ?MediaIngestionOutboxDelivery;

    public function markDelivered(MediaIngestionOutboxDelivery $delivery, DateTimeImmutable $at): bool;

    public function scheduleRetry(
        MediaIngestionOutboxDelivery $delivery,
        DateTimeImmutable $availableAt,
        string $errorCode,
    ): bool;

    public function quarantine(MediaIngestionOutboxDelivery $delivery, string $errorCode): bool;
}
