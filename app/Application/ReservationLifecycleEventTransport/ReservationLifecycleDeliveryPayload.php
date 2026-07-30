<?php

namespace App\Application\ReservationLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEvent;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventAggregateType;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventId;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventMetadata;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventPayload;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventPayloadVersion;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventSerializer;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventType;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Throwable;
use UnexpectedValueException;

final readonly class ReservationLifecycleDeliveryPayload implements PublicProjectionDeliveryPayload
{
    private string $canonicalEvent;

    public function __construct(public ReservationLifecycleEvent $event)
    {
        $this->canonicalEvent = (new ReservationLifecycleEventSerializer)->serialize($event);
    }

    /** @return array{canonicalEvent:string} */
    public function fields(): array
    {
        return ['canonicalEvent' => $this->canonicalEvent];
    }

    public function checksum(): string
    {
        return hash('sha256', $this->canonicalEvent);
    }

    /** @param array<mixed> $fields */
    public static function restore(array $fields): self
    {
        if (array_keys($fields) !== ['canonicalEvent'] || ! is_string($fields['canonicalEvent'])) {
            throw new UnexpectedValueException('Reservation lifecycle delivery payload envelope is invalid.');
        }

        try {
            $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data) || array_keys($data) !== [
                'eventId',
                'aggregateType',
                'eventType',
                'payloadVersion',
                'reservationId',
                'transition',
                'previousState',
                'currentState',
                'action',
                'version',
                'occurredVersion',
            ]) {
                throw new UnexpectedValueException('Canonical reservation lifecycle event shape is invalid.');
            }

            $eventType = ReservationLifecycleEventType::from(self::string($data, 'eventType'));
            $payloadVersion = ReservationLifecycleEventPayloadVersion::from(self::integer($data, 'payloadVersion'));
            $reservationId = ReservationId::fromString(self::string($data, 'reservationId'));
            $previousState = ReservationLifecycleState::from(self::string($data, 'previousState'));
            $currentState = ReservationLifecycleState::from(self::string($data, 'currentState'));
            $action = ReservationLifecycleAction::from(self::string($data, 'action'));
            $transition = new ReservationLifecycleTransition($previousState, $currentState, $action);
            $occurredVersion = self::integer($data, 'occurredVersion');
            $eventId = ReservationLifecycleEventId::derive($eventType, $payloadVersion, $reservationId, $transition, $occurredVersion);

            if ($eventId->value !== self::string($data, 'eventId')) {
                throw new UnexpectedValueException('Reservation lifecycle event identity is inconsistent.');
            }

            $restored = new self(new ReservationLifecycleEvent(
                new ReservationLifecycleEventMetadata($eventType, $payloadVersion),
                new ReservationLifecycleEventPayload(
                    $eventId,
                    ReservationLifecycleEventAggregateType::from(self::string($data, 'aggregateType')),
                    $reservationId,
                    self::string($data, 'transition'),
                    $previousState,
                    $currentState,
                    $action,
                    self::integer($data, 'version'),
                    $occurredVersion,
                ),
            ));

            if ($restored->canonicalEvent !== $fields['canonicalEvent']) {
                throw new UnexpectedValueException('Reservation lifecycle event serialization is not canonical.');
            }

            return $restored;
        } catch (UnexpectedValueException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new UnexpectedValueException('Reservation lifecycle delivery payload cannot be restored.', previous: $error);
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new UnexpectedValueException("Reservation lifecycle event field {$field} must be a string.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new UnexpectedValueException("Reservation lifecycle event field {$field} must be an integer.");
        }

        return $data[$field];
    }
}
