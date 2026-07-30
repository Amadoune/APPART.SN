<?php

namespace App\Application\AccountStatusEventTransport;

final readonly class AccountStatusDeliveryMetadata
{
    public const string SOURCE = 'AccountStatus';

    private function __construct(
        public string $source,
        public string $eventId,
        public AccountStatusTransportChecksum $payloadChecksum,
    ) {
        if ($source !== self::SOURCE || preg_match('/^[a-f0-9]{64}$/', $eventId) !== 1) {
            throw new AccountStatusEventTransportException('Account Status delivery metadata is invalid.');
        }
    }

    public static function fromPayload(AccountStatusDeliveryPayload $payload): self
    {
        return new self(self::SOURCE, $payload->event->eventId->value, $payload->transportChecksum());
    }

    /** @return array{source: string, eventId: string, payloadChecksum: string} */
    public function fields(): array
    {
        return [
            'source' => $this->source,
            'eventId' => $this->eventId,
            'payloadChecksum' => $this->payloadChecksum->value,
        ];
    }
}
