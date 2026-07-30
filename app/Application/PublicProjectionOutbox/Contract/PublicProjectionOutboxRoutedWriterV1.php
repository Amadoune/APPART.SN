<?php

namespace App\Application\PublicProjectionOutbox\Contract;

use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;

interface PublicProjectionOutboxRoutedWriterV1
{
    public function appendRouted(
        PublicProjectionRoutedDeliveryMessageV1 $delivery,
        PublicProjectionOutboxConsumerId $consumerId,
    ): PublicProjectionOutboxWriteResult;
}
