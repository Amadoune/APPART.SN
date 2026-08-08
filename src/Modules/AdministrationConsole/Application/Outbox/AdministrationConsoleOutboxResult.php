<?php

namespace Appart\Modules\AdministrationConsole\Application\Outbox;

use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryV1;

final readonly class AdministrationConsoleOutboxResult
{
    public function __construct(
        public string $messageId,
        public AdministrationConsoleOutboxStatus $status,
        public AdministrationOperatorDeliveryV1|AdministrationQueueDeliveryV1|AdministrationAuditDeliveryV1 $delivery,
        public int $retryCount,
    ) {}
}
