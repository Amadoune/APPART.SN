<?php

namespace Appart\Modules\Notifications\Application\Outbox;

use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryV1;

interface NotificationOutboxWriter
{
    public function append(NotificationDeliveryV1 $delivery): NotificationOutboxResult;
}
