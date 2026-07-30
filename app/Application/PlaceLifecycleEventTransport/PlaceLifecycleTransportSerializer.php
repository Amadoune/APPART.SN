<?php

namespace App\Application\PlaceLifecycleEventTransport;

use Throwable;

final readonly class PlaceLifecycleTransportSerializer
{
    public function serialize(PlaceLifecycleTransportEnvelope $envelope): string
    {
        return json_encode([
            'messageId' => $envelope->messageId->value,
            'messageType' => $envelope->messageType,
            'transportVersion' => $envelope->transportVersion->value,
            'payload' => $envelope->payload->fields(),
            'metadata' => $envelope->metadata->fields(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function restore(string $serialized): PlaceLifecycleTransportEnvelope
    {
        try {
            $data = json_decode($serialized, true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['messageId', 'messageType', 'transportVersion', 'payload', 'metadata']
                || ! is_array($data['payload'])
                || ! is_array($data['metadata'])
                || array_keys($data['metadata']) !== ['source', 'eventId', 'payloadChecksum']) {
                throw new PlaceLifecycleEventTransportException('Place Lifecycle transport shape is invalid.');
            }

            $payload = PlaceLifecycleDeliveryPayload::restore($data['payload']);
            $envelope = PlaceLifecycleTransportEnvelope::wrap($payload);

            if (
                ! is_string($data['messageId'])
                || ! is_string($data['messageType'])
                || ! is_int($data['transportVersion'])
                || ! is_string($data['metadata']['source'])
                || ! is_string($data['metadata']['eventId'])
                || ! is_string($data['metadata']['payloadChecksum'])
                || $data['messageId'] !== $envelope->messageId->value
                || $data['messageType'] !== $envelope->messageType
                || $data['transportVersion'] !== $envelope->transportVersion->value
                || $data['metadata'] !== $envelope->metadata->fields()
                || $this->serialize($envelope) !== $serialized
            ) {
                throw new PlaceLifecycleEventTransportException('Place Lifecycle transport is not canonical.');
            }

            return $envelope;
        } catch (PlaceLifecycleEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new PlaceLifecycleEventTransportException(
                'Place Lifecycle transport cannot be restored.',
                previous: $error,
            );
        }
    }
}
