<?php

namespace App\Application\MediaIngestionEventDelivery;

use App\Application\MediaIngestionEventRouting\MediaIngestionRoutingDestination;
use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;
use App\Application\MediaIngestionEventTransport\MediaIngestionEventTransportSerializer;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventType;
use Throwable;

final readonly class MediaIngestionDeliveryConsumer
{
    public function __construct(private MediaIngestionEventTransportSerializer $serializer) {}

    public function consume(MediaIngestionDeliveryMessageV1 $message, MediaIngestionRoutingDestination $destination): MediaIngestionConsumedFact
    {
        try {
            $restored = $this->serializer->restore($this->serializer->serialize($message));
        } catch (Throwable $error) {
            throw new \UnexpectedValueException('Corrupted Media Ingestion delivery.', previous: $error);
        }
        if (! $this->supports($restored->event->type, $destination)) {
            throw new \UnexpectedValueException('Unsupported Media Ingestion destination.');
        }

        return new MediaIngestionConsumedFact($restored->messageId, $restored->payloadChecksum, $destination, $restored->event);
    }

    private function supports(MediaIngestionEventType $type, MediaIngestionRoutingDestination $destination): bool
    {
        return match ($destination) {
            MediaIngestionRoutingDestination::PrivateAudit => true,
            MediaIngestionRoutingDestination::AuthoringStatus => in_array($type, [MediaIngestionEventType::AssetReady, MediaIngestionEventType::AssetRejected], true),
            MediaIngestionRoutingDestination::MediaAttachment => $type === MediaIngestionEventType::AssetReady,
            MediaIngestionRoutingDestination::Reconciliation => $type === MediaIngestionEventType::AssetPurged,
        };
    }
}
