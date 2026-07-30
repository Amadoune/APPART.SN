<?php

namespace App\Application\PublicProjectionDelivery\Contract;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;

interface PublicProjectionRoutedDeliveryConsumerV1
{
    public function consumeRouted(
        PublicProjectionRoutedDeliveryMessageV1 $delivery,
    ): PublicProjectionDeliveryConsumptionResult;
}
