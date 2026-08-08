<?php

namespace Appart\Modules\AdministrationConsole\Application\Outbox;

use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryV1;

final readonly class AdministrationConsoleOutboxPolicy
{
    public const MAX_RETRIES = 10;

    public function prepare(AdministrationOperatorDeliveryV1|AdministrationQueueDeliveryV1|AdministrationAuditDeliveryV1 $delivery): AdministrationConsoleOutboxResult
    {
        return new AdministrationConsoleOutboxResult($this->messageId($delivery), AdministrationConsoleOutboxStatus::Applied, $delivery, 0);
    }

    public function messageId(AdministrationOperatorDeliveryV1|AdministrationQueueDeliveryV1|AdministrationAuditDeliveryV1 $delivery): string
    {
        return hash('sha256', $this->canonical($delivery));
    }

    public function checksum(AdministrationOperatorDeliveryV1|AdministrationQueueDeliveryV1|AdministrationAuditDeliveryV1 $delivery): string
    {
        return hash('sha256', "administration-console-outbox-v1\n".$this->canonical($delivery));
    }

    public function canonical(AdministrationOperatorDeliveryV1|AdministrationQueueDeliveryV1|AdministrationAuditDeliveryV1 $delivery): string
    {
        return json_encode([
            'type' => $delivery->type->value,
            'status' => $delivery->payload->status->value,
            'observedAt' => $delivery->payload->observedAt,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
