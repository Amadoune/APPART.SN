<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventV1;

final readonly class AdministrationQueueDeliveryFactory
{
    public function create(AdministrationQueueEventV1 $event): AdministrationQueueDeliveryResult
    {
        $status = match ($event->payload->status) {
            AdministrationQueueEventStatus::Ready => AdministrationQueueDeliveryStatus::Ready,
            AdministrationQueueEventStatus::Empty => AdministrationQueueDeliveryStatus::Empty,
            AdministrationQueueEventStatus::Missing => AdministrationQueueDeliveryStatus::Missing,
            AdministrationQueueEventStatus::Corrupted => AdministrationQueueDeliveryStatus::Corrupted,
            AdministrationQueueEventStatus::DependencyUnavailable => AdministrationQueueDeliveryStatus::DependencyUnavailable,
        };

        return new AdministrationQueueDeliveryResult(new AdministrationQueueDeliveryV1($event->type, new AdministrationQueueDeliveryPayload($status, $event->payload->observedAt)));
    }
}
