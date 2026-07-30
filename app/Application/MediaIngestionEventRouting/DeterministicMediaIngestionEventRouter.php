<?php

namespace App\Application\MediaIngestionEventRouting;

use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;
use App\Application\MediaIngestionEventTransport\MediaIngestionEventTransportSerializer;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventType;
use Throwable;

final readonly class DeterministicMediaIngestionEventRouter
{
    public function __construct(private MediaIngestionEventTransportSerializer $serializer) {}

    public function route(MediaIngestionDeliveryMessageV1 $message): MediaIngestionRoutingResult
    {
        try {
            $restored = $this->serializer->restore($this->serializer->serialize($message));
            if ($restored->fields() !== $message->fields()) {
                return MediaIngestionRoutingResult::rejected($message);
            }
        } catch (Throwable) {
            return MediaIngestionRoutingResult::rejected($message);
        }

        return MediaIngestionRoutingResult::routed($message, match ($message->event->type) {
            MediaIngestionEventType::AssetReady => [
                MediaIngestionRoutingDestination::PrivateAudit,
                MediaIngestionRoutingDestination::AuthoringStatus,
                MediaIngestionRoutingDestination::MediaAttachment,
            ],
            MediaIngestionEventType::AssetRejected => [
                MediaIngestionRoutingDestination::PrivateAudit,
                MediaIngestionRoutingDestination::AuthoringStatus,
            ],
            MediaIngestionEventType::AssetPurged => [
                MediaIngestionRoutingDestination::PrivateAudit,
                MediaIngestionRoutingDestination::Reconciliation,
            ],
        });
    }
}
