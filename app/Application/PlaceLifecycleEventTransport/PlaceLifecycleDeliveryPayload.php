<?php

namespace App\Application\PlaceLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload as PublicProjectionDeliveryPayloadContract;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventId;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventPayloadV1;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventPayloadVersion;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventType;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use DateTimeImmutable;
use Throwable;

final readonly class PlaceLifecycleDeliveryPayload implements PublicProjectionDeliveryPayloadContract
{
    private string $canonicalEvent;

    public function __construct(public PlaceLifecycleEventV1 $event)
    {
        $this->canonicalEvent = self::canonicalJson($event->contract());
    }

    /** @return array{canonicalEvent:string} */
    public function fields(): array
    {
        return ['canonicalEvent' => $this->canonicalEvent];
    }

    public function transportChecksum(): PlaceLifecycleTransportChecksum
    {
        return PlaceLifecycleTransportChecksum::fromCanonicalPayload($this->canonicalEvent);
    }

    public function checksum(): string
    {
        return $this->transportChecksum()->value;
    }

    /** @param array<mixed> $fields */
    public static function restore(array $fields): self
    {
        if (array_keys($fields) !== ['canonicalEvent'] || ! is_string($fields['canonicalEvent'])) {
            throw new PlaceLifecycleEventTransportException('Place Lifecycle delivery payload shape is invalid.');
        }

        try {
            $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['eventId', 'eventType', 'payloadVersion', 'occurredAt', 'payload']
                || ! is_array($data['payload'])) {
                throw new PlaceLifecycleEventTransportException('Canonical Place Lifecycle event shape is invalid.');
            }

            $type = PlaceLifecycleEventType::from(self::string($data, 'eventType'));
            $payloadVersion = PlaceLifecycleEventPayloadVersion::from(self::integer($data, 'payloadVersion'));
            $payloadData = $data['payload'];
            $expectedPayloadKeys = $type === PlaceLifecycleEventType::Merged
                ? ['placeId', 'previousState', 'action', 'currentState', 'occurredVersion', 'targetPlaceId']
                : ['placeId', 'previousState', 'action', 'currentState', 'occurredVersion'];

            if (array_keys($payloadData) !== $expectedPayloadKeys) {
                throw new PlaceLifecycleEventTransportException('Canonical Place Lifecycle payload shape is invalid.');
            }

            $placeId = PlaceId::fromString(self::string($payloadData, 'placeId'));
            $previousState = PlaceLifecycleState::from(self::string($payloadData, 'previousState'));
            $action = PlaceLifecycleAction::from(self::string($payloadData, 'action'));
            $currentState = PlaceLifecycleState::from(self::string($payloadData, 'currentState'));
            $occurredVersion = self::integer($payloadData, 'occurredVersion');
            $targetPlaceId = $type === PlaceLifecycleEventType::Merged
                ? PlaceId::fromString(self::string($payloadData, 'targetPlaceId'))
                : null;
            $transition = new PlaceLifecycleTransition($previousState, $action, $currentState);
            $eventId = PlaceLifecycleEventId::derive(
                $type,
                $payloadVersion,
                $placeId,
                $transition,
                $occurredVersion,
                $targetPlaceId,
            );

            if ($eventId->value !== self::string($data, 'eventId')) {
                throw new PlaceLifecycleEventTransportException('Place Lifecycle business event identity is inconsistent.');
            }

            $restored = new self(new PlaceLifecycleEventV1(
                $type,
                $eventId,
                PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable(self::string($data, 'occurredAt'))),
                new PlaceLifecycleEventPayloadV1(
                    $eventId,
                    $placeId,
                    $previousState,
                    $action,
                    $currentState,
                    $occurredVersion,
                    $targetPlaceId,
                ),
            ));

            if ($restored->canonicalEvent !== $fields['canonicalEvent']) {
                throw new PlaceLifecycleEventTransportException('Place Lifecycle event is not canonically encoded.');
            }

            return $restored;
        } catch (PlaceLifecycleEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new PlaceLifecycleEventTransportException(
                'Place Lifecycle delivery payload cannot be restored.',
                previous: $error,
            );
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new PlaceLifecycleEventTransportException("Place Lifecycle field {$field} must be a string.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new PlaceLifecycleEventTransportException("Place Lifecycle field {$field} must be an integer.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function canonicalJson(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
