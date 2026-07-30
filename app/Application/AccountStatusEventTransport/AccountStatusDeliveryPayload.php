<?php

namespace App\Application\AccountStatusEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventId;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventPayloadV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventPayloadVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventType;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Throwable;

final readonly class AccountStatusDeliveryPayload implements PublicProjectionDeliveryPayload
{
    private string $canonicalEvent;

    public function __construct(public AccountStatusEventV1 $event)
    {
        $this->canonicalEvent = self::canonicalJson($event->contract());
    }

    /** @return array{canonicalEvent: string} */
    public function fields(): array
    {
        return ['canonicalEvent' => $this->canonicalEvent];
    }

    public function transportChecksum(): AccountStatusTransportChecksum
    {
        return AccountStatusTransportChecksum::fromCanonicalPayload($this->canonicalEvent);
    }

    public function checksum(): string
    {
        return $this->transportChecksum()->value;
    }

    /** @param array<mixed> $fields */
    public static function restore(array $fields): self
    {
        if (array_keys($fields) !== ['canonicalEvent'] || ! is_string($fields['canonicalEvent'])) {
            throw new AccountStatusEventTransportException('Account Status delivery payload shape is invalid.');
        }

        try {
            $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['eventId', 'eventType', 'payloadVersion', 'occurredAt', 'payload']
                || ! is_array($data['payload'])
                || array_keys($data['payload']) !== ['accountId', 'previousState', 'action', 'currentState', 'occurredVersion']) {
                throw new AccountStatusEventTransportException('Canonical Account Status event shape is invalid.');
            }

            $type = AccountStatusEventType::from(self::string($data, 'eventType'));
            $payloadVersion = AccountStatusEventPayloadVersion::from(self::integer($data, 'payloadVersion'));
            $payloadData = $data['payload'];
            $accountId = AccountId::fromString(self::string($payloadData, 'accountId'));
            $previousState = AccountStatusState::from(self::string($payloadData, 'previousState'));
            $action = AccountStatusAction::from(self::string($payloadData, 'action'));
            $currentState = AccountStatusState::from(self::string($payloadData, 'currentState'));
            $occurredVersion = self::integer($payloadData, 'occurredVersion');
            $transition = new AccountStatusTransition($previousState, $action, $currentState);
            $eventId = AccountStatusEventId::derive(
                $type,
                $payloadVersion,
                $accountId,
                $transition,
                $occurredVersion,
            );
            if ($eventId->value !== self::string($data, 'eventId')) {
                throw new AccountStatusEventTransportException('Account Status business event identity is inconsistent.');
            }

            $restored = new self(new AccountStatusEventV1(
                $type,
                $eventId,
                new AccountStatusOccurredAt(new DateTimeImmutable(self::string($data, 'occurredAt'))),
                new AccountStatusEventPayloadV1(
                    $eventId,
                    $accountId,
                    $previousState,
                    $action,
                    $currentState,
                    $occurredVersion,
                ),
            ));
            if ($restored->canonicalEvent !== $fields['canonicalEvent']) {
                throw new AccountStatusEventTransportException('Account Status event is not canonically encoded.');
            }

            return $restored;
        } catch (AccountStatusEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new AccountStatusEventTransportException(
                'Account Status delivery payload cannot be restored.',
                previous: $error,
            );
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new AccountStatusEventTransportException("Account Status field {$field} must be a string.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new AccountStatusEventTransportException("Account Status field {$field} must be an integer.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function canonicalJson(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
