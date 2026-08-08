<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventStatus;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventV1;

final readonly class OperationalSeoDeliveryFactory
{
    public function create(OperationalSeoEventV1 $event): OperationalSeoDeliveryResult
    {
        $status = match ($event->payload->status) {
            OperationalSeoEventStatus::Indexable => OperationalSeoDeliveryStatus::Indexable,
            OperationalSeoEventStatus::NoIndex => OperationalSeoDeliveryStatus::NoIndex,
            OperationalSeoEventStatus::Missing => OperationalSeoDeliveryStatus::Missing,
            OperationalSeoEventStatus::Corrupted => OperationalSeoDeliveryStatus::Corrupted,
            OperationalSeoEventStatus::DependencyUnavailable => OperationalSeoDeliveryStatus::DependencyUnavailable,
        };

        return new OperationalSeoDeliveryResult(new OperationalSeoDeliveryV1(new OperationalSeoDeliveryPayload($event->type, $status, $event->payload->observedAt)));
    }
}
