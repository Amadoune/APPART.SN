<?php

namespace App\Application\ModerationEventTransport;

use UnexpectedValueException;

final class ModerationEventTransportSerializer
{
    public function serialize(ModerationDeliveryMessageV1 $message): string
    {
        return json_encode($message->fields(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function validate(ModerationDeliveryMessageV1 $message, string $serialized): void
    {
        if (! hash_equals($this->serialize($message), $serialized)) {
            throw new UnexpectedValueException('Non-canonical Moderation transport.');
        }
        $decoded = json_decode($serialized, true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($decoded) || $decoded !== $message->fields()) {
            throw new UnexpectedValueException('Corrupted Moderation transport.');
        }
    }
}
