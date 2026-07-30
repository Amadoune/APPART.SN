<?php

namespace App\Application\AccountStatusEventTransport;

use Throwable;

final readonly class AccountStatusTransportSerializer
{
    public function serialize(AccountStatusDeliveryMessage $message): string
    {
        return json_encode([
            'messageId' => $message->messageId->value,
            'messageType' => $message->messageType,
            'transportVersion' => $message->transportVersion->value,
            'payload' => $message->payload->fields(),
            'metadata' => $message->metadata->fields(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function restore(string $serialized): AccountStatusDeliveryMessage
    {
        try {
            $data = json_decode($serialized, true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['messageId', 'messageType', 'transportVersion', 'payload', 'metadata']
                || ! is_array($data['payload'])
                || ! is_array($data['metadata'])
                || array_keys($data['metadata']) !== ['source', 'eventId', 'payloadChecksum']) {
                throw new AccountStatusEventTransportException('Account Status transport shape is invalid.');
            }

            $message = AccountStatusDeliveryMessage::wrap(AccountStatusDeliveryPayload::restore($data['payload']));
            if (
                ! is_string($data['messageId'])
                || ! is_string($data['messageType'])
                || ! is_int($data['transportVersion'])
                || ! is_string($data['metadata']['source'])
                || ! is_string($data['metadata']['eventId'])
                || ! is_string($data['metadata']['payloadChecksum'])
                || $data['messageId'] !== $message->messageId->value
                || $data['messageType'] !== $message->messageType
                || $data['transportVersion'] !== $message->transportVersion->value
                || $data['metadata'] !== $message->metadata->fields()
                || $this->serialize($message) !== $serialized
            ) {
                throw new AccountStatusEventTransportException('Account Status transport is not canonical.');
            }

            return $message;
        } catch (AccountStatusEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new AccountStatusEventTransportException(
                'Account Status transport cannot be restored.',
                previous: $error,
            );
        }
    }
}
