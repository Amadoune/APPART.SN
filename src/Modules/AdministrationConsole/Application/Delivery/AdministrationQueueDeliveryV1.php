<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventType;

final readonly class AdministrationQueueDeliveryV1
{
    public function __construct(public AdministrationQueueEventType $type, public AdministrationQueueDeliveryPayload $payload) {}
}
