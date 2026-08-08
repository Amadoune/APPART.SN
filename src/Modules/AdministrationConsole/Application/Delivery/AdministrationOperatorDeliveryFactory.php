<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventV1;

final readonly class AdministrationOperatorDeliveryFactory
{
    public function create(AdministrationOperatorEventV1 $event): AdministrationOperatorDeliveryResult
    {
        $status = match ($event->payload->status) {
            AdministrationOperatorEventStatus::Available => AdministrationOperatorDeliveryStatus::Available,
            AdministrationOperatorEventStatus::Unavailable => AdministrationOperatorDeliveryStatus::Unavailable,
            AdministrationOperatorEventStatus::Missing => AdministrationOperatorDeliveryStatus::Missing,
            AdministrationOperatorEventStatus::Corrupted => AdministrationOperatorDeliveryStatus::Corrupted,
            AdministrationOperatorEventStatus::DependencyUnavailable => AdministrationOperatorDeliveryStatus::DependencyUnavailable,
        };

        return new AdministrationOperatorDeliveryResult(new AdministrationOperatorDeliveryV1($event->type, new AdministrationOperatorDeliveryPayload($status, $event->payload->observedAt)));
    }
}
