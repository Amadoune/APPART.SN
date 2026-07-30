<?php

namespace App\Application\AccountStatusEventTransport;

final readonly class AccountStatusDeliveryMessage
{
    private function __construct(
        public AccountStatusMessageId $messageId,
        public string $messageType,
        public AccountStatusTransportVersion $transportVersion,
        public AccountStatusDeliveryPayload $payload,
        public AccountStatusDeliveryMetadata $metadata,
    ) {
        $derivedMessageId = AccountStatusMessageId::derive($transportVersion, $payload->transportChecksum());
        if (
            $messageId->value !== $derivedMessageId->value
            || $messageType !== $payload->event->type->value
            || $metadata->eventId !== $payload->event->eventId->value
            || $metadata->payloadChecksum->value !== $payload->transportChecksum()->value
        ) {
            throw new AccountStatusEventTransportException('Account Status delivery message is inconsistent.');
        }
    }

    public static function wrap(AccountStatusDeliveryPayload $payload): self
    {
        $version = AccountStatusTransportVersion::V1;

        return new self(
            AccountStatusMessageId::derive($version, $payload->transportChecksum()),
            $payload->event->type->value,
            $version,
            $payload,
            AccountStatusDeliveryMetadata::fromPayload($payload),
        );
    }
}
