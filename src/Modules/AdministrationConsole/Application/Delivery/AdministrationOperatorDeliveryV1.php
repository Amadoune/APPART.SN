<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventType;

final readonly class AdministrationOperatorDeliveryV1
{
    public function __construct(public AdministrationOperatorEventType $type, public AdministrationOperatorDeliveryPayload $payload) {}
}
