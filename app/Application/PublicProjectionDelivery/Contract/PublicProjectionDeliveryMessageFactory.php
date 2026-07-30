<?php

namespace App\Application\PublicProjectionDelivery\Contract;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use DateTimeImmutable;

interface PublicProjectionDeliveryMessageFactory
{
    public function create(PublicProjectionDeliveryPublishableFact $fact, DateTimeImmutable $recordedAt): PublicProjectionDeliveryMessage;
}
