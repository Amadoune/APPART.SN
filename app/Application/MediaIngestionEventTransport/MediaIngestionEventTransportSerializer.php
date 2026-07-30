<?php

namespace App\Application\MediaIngestionEventTransport;

use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventType;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventV1;
use DateTimeImmutable;
use Throwable;

final class MediaIngestionEventTransportSerializer
{
    public function serialize(MediaIngestionDeliveryMessageV1 $message): string
    {
        return json_encode($message->fields(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function restore(string $serialized): MediaIngestionDeliveryMessageV1
    {
        try {
            $data = json_decode($serialized, true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data) || array_keys($data) !== ['messageId', 'messageType', 'transportVersion', 'payload', 'metadata']
                || ! is_array($data['payload']) || ! is_array($data['metadata'])) {
                throw new MediaIngestionEventTransportException('Invalid Media Ingestion transport shape.');
            }
            $payload = $data['payload'];
            $event = new MediaIngestionEventV1(
                MediaIngestionEventType::from(self::string($payload, 'eventType')),
                self::string($payload, 'assetId'),
                self::integer($payload, 'aggregateVersion'),
                self::string($payload, 'policyVersion'),
                new DateTimeImmutable(self::string($payload, 'occurredAt')),
                new DateTimeImmutable(self::string($payload, 'recordedAt')),
                self::string($payload, 'correlationId'),
                self::string($payload, 'causationId'),
            );
            $restored = new MediaIngestionDeliveryMessageV1($event);
            if ($restored->event->contract() !== $payload || $restored->fields() !== $data || $this->serialize($restored) !== $serialized) {
                throw new MediaIngestionEventTransportException('Media Ingestion transport is divergent or non-canonical.');
            }

            return $restored;
        } catch (MediaIngestionEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new MediaIngestionEventTransportException('Media Ingestion transport cannot be restored.', previous: $error);
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $key): string
    {
        if (! isset($data[$key]) || ! is_string($data[$key])) {
            throw new MediaIngestionEventTransportException("Field {$key} must be a string.");
        }

        return $data[$key];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $key): int
    {
        if (! isset($data[$key]) || ! is_int($data[$key])) {
            throw new MediaIngestionEventTransportException("Field {$key} must be an integer.");
        }

        return $data[$key];
    }
}
