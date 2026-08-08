<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventStatus;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventV1;

final readonly class EditorialContentDeliveryFactory
{
    public function create(EditorialContentEventV1 $event): EditorialContentDeliveryResult
    {
        $status = match ($event->payload->status) {
            EditorialContentEventStatus::Published => EditorialContentDeliveryStatus::Published,
            EditorialContentEventStatus::Unpublished => EditorialContentDeliveryStatus::Unpublished,
            EditorialContentEventStatus::Missing => EditorialContentDeliveryStatus::Missing,
            EditorialContentEventStatus::Corrupted => EditorialContentDeliveryStatus::Corrupted,
            EditorialContentEventStatus::DependencyUnavailable => EditorialContentDeliveryStatus::DependencyUnavailable,
        };

        return new EditorialContentDeliveryResult(new EditorialContentDeliveryV1(new EditorialContentDeliveryPayload($event->type, $status, $event->payload->observedAt)));
    }
}
