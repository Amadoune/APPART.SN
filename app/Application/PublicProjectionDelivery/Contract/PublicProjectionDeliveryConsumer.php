<?php

namespace App\Application\PublicProjectionDelivery\Contract;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;

interface PublicProjectionDeliveryConsumer
{
    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult;
}
