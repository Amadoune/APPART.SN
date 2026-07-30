<?php

namespace App\Application\ModerationEventDelivery\Contract;

use App\Application\ModerationEventDelivery\ModerationDeliveryResult;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use DateTimeImmutable;

interface ModerationDeliveryStore
{
    public function deliver(ModerationDeliveryMessageV1 $message, ModerationRoutingDestination $destination, DateTimeImmutable $at): ModerationDeliveryResult;

    public function fail(ModerationDeliveryMessageV1 $message, ModerationRoutingDestination $destination, int $attempt, DateTimeImmutable $at): ModerationDeliveryResult;
}
