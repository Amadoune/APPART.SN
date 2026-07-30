<?php

namespace App\Application\PublicProjectionOutbox\Contract;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimResult;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxLease;
use DateTimeImmutable;

interface PublicProjectionOutboxClaimManager
{
    public function claim(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxLease $lease): PublicProjectionOutboxClaimResult;

    public function expire(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, DateTimeImmutable $at): PublicProjectionOutboxClaimResult;

    public function abandon(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxLease $lease): PublicProjectionOutboxClaimResult;
}
