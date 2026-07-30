<?php

namespace App\Application\ModerationEventOutbox\Contract;

use App\Application\ModerationEventOutbox\ModerationOutboxDelivery;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use DateTimeImmutable;

interface ModerationOutboxReaderV1
{
    public function read(string $messageId, string $destination): ?ModerationOutboxDelivery;

    public function claimNext(string $owner, DateTimeImmutable $now): ?ModerationOutboxDelivery;

    public function claimNextForDestination(
        string $owner,
        ModerationRoutingDestination $destination,
        DateTimeImmutable $now,
    ): ?ModerationOutboxDelivery;

    public function release(ModerationOutboxDelivery $delivery, DateTimeImmutable $availableAt): bool;

    public function retry(
        ModerationOutboxDelivery $delivery,
        DateTimeImmutable $availableAt,
        string $errorCode,
    ): bool;

    public function markDelivered(ModerationOutboxDelivery $delivery, DateTimeImmutable $at): bool;

    public function quarantine(ModerationOutboxDelivery $delivery, string $errorCode): bool;

    public function replay(string $messageId, string $destination, DateTimeImmutable $at): bool;
}
