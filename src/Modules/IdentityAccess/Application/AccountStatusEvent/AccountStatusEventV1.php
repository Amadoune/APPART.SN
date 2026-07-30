<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusEvent;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use InvalidArgumentException;

final readonly class AccountStatusEventV1
{
    public function __construct(
        public AccountStatusEventType $type,
        public AccountStatusEventId $eventId,
        public AccountStatusOccurredAt $occurredAt,
        public AccountStatusEventPayloadV1 $payload,
    ) {
        $certifiedType = (new AccountStatusEventCatalog)->typeFor($payload->transition());
        $derivedId = AccountStatusEventId::derive(
            $type,
            $payload->version,
            $payload->accountId,
            $payload->transition(),
            $payload->occurredVersion,
        );

        if (
            $certifiedType !== $type
            || $eventId->value !== $payload->eventId->value
            || $eventId->value !== $derivedId->value
        ) {
            throw new InvalidArgumentException('Inconsistent Account Status event contract.');
        }
    }

    /**
     * @return array{
     *     eventId: string,
     *     eventType: string,
     *     payloadVersion: int,
     *     occurredAt: string,
     *     payload: array{accountId: string, previousState: string, action: string, currentState: string, occurredVersion: int}
     * }
     */
    public function contract(): array
    {
        return [
            'eventId' => $this->eventId->value,
            'eventType' => $this->type->value,
            'payloadVersion' => $this->payload->version->value,
            'occurredAt' => $this->occurredAt->value->format('Y-m-d\TH:i:s.uP'),
            'payload' => $this->payload->fields(),
        ];
    }
}
