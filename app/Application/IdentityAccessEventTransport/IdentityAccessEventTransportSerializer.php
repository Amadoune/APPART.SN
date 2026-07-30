<?php

namespace App\Application\IdentityAccessEventTransport;

use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventType;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Throwable;

final class IdentityAccessEventTransportSerializer
{
    public function serialize(IdentityAccessDeliveryMessageV1 $message): string
    {
        return json_encode($message->fields(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function restore(string $serialized): IdentityAccessDeliveryMessageV1
    {
        try {
            $data = json_decode($serialized, true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data) || array_keys($data) !== ['messageId', 'messageType', 'transportVersion', 'payload', 'metadata']
                || ! is_array($data['payload']) || ! is_array($data['metadata'])) {
                throw new IdentityAccessEventTransportException('Invalid IAM transport shape.');
            }
            $payload = $data['payload'];
            $event = new IdentityAccessEventV1(
                IdentityAccessEventType::from(self::string($payload, 'eventType')),
                AccountId::fromString(self::string($payload, 'accountId')),
                self::integer($payload, 'aggregateVersion'),
                new DateTimeImmutable(self::string($payload, 'occurredAt')),
                new DateTimeImmutable(self::string($payload, 'recordedAt')),
                self::string($payload, 'correlationId'),
                self::string($payload, 'causationId'),
            );
            $restored = new IdentityAccessDeliveryMessageV1($event);
            if ($restored->event->contract() !== $payload
                || $restored->fields() !== $data
                || $this->serialize($restored) !== $serialized) {
                throw new IdentityAccessEventTransportException('IAM transport is divergent or non-canonical.');
            }

            return $restored;
        } catch (IdentityAccessEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new IdentityAccessEventTransportException('IAM transport cannot be restored.', previous: $error);
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $key): string
    {
        if (! isset($data[$key]) || ! is_string($data[$key])) {
            throw new IdentityAccessEventTransportException("IAM field {$key} must be a string.");
        }

        return $data[$key];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $key): int
    {
        if (! isset($data[$key]) || ! is_int($data[$key])) {
            throw new IdentityAccessEventTransportException("IAM field {$key} must be an integer.");
        }

        return $data[$key];
    }
}
