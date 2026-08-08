<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventStatus;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventV1;

final readonly class AdministrationAuditDeliveryFactory
{
    public function create(AdministrationAuditEventV1 $event): AdministrationAuditDeliveryResult
    {
        $status = match ($event->payload->status) {
            AdministrationAuditEventStatus::Available => AdministrationAuditDeliveryStatus::Available,
            AdministrationAuditEventStatus::Missing => AdministrationAuditDeliveryStatus::Missing,
            AdministrationAuditEventStatus::Corrupted => AdministrationAuditDeliveryStatus::Corrupted,
            AdministrationAuditEventStatus::DependencyUnavailable => AdministrationAuditDeliveryStatus::DependencyUnavailable,
        };

        return new AdministrationAuditDeliveryResult(new AdministrationAuditDeliveryV1($event->type, new AdministrationAuditDeliveryPayload($status, $event->payload->observedAt)));
    }
}
