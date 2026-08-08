<?php

namespace Appart\Modules\AdministrationConsole\Application\Outbox;

use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryV1;

interface AdministrationConsoleOutboxWriter
{
    public function append(AdministrationOperatorDeliveryV1|AdministrationQueueDeliveryV1|AdministrationAuditDeliveryV1 $delivery): AdministrationConsoleOutboxResult;
}
