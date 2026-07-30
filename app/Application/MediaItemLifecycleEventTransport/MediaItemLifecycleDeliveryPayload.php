<?php

namespace App\Application\MediaItemLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEvent;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventId;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventMetadata;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayload;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayloadVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventSerializer;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventType;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use DateTimeImmutable;
use Throwable;

final readonly class MediaItemLifecycleDeliveryPayload implements PublicProjectionDeliveryPayload
{
    private string $canonicalEvent;

    public function __construct(public MediaItemLifecycleEvent $event)
    {
        $this->canonicalEvent = (new MediaItemLifecycleEventSerializer)->serialize($event);
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
            throw new MediaItemLifecycleEventTransportException('Media item lifecycle delivery payload envelope is invalid.');
        }

        try {
            $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata']
                || ! is_array($data['payload'])
                || ! is_array($data['metadata'])
                || array_keys($data['payload']) !== ['eventId', 'aggregateType', 'mediaId', 'transition', 'previousState', 'currentState', 'action', 'version', 'occurredVersion']
                || array_keys($data['metadata']) !== ['eventType', 'payloadVersion', 'actorId', 'occurredAt', 'recordedAt']) {
                throw new MediaItemLifecycleEventTransportException('Canonical media item lifecycle event shape is invalid.');
            }

            $eventType = MediaItemLifecycleEventType::from(self::string($data, 'eventType'));
            $payloadVersion = MediaItemLifecycleEventPayloadVersion::from(self::integer($data, 'payloadVersion'));
            $mediaId = MediaItemLifecycleId::fromString(self::string($data['payload'], 'mediaId'));
            $previousState = MediaItemLifecycleState::from(self::string($data['payload'], 'previousState'));
            $currentState = MediaItemLifecycleState::from(self::string($data['payload'], 'currentState'));
            $action = MediaItemLifecycleAction::from(self::string($data['payload'], 'action'));
            $transition = new MediaItemLifecycleTransition($previousState, $currentState, $action);
            $eventId = MediaItemLifecycleEventId::derive($eventType, $payloadVersion, $mediaId, $transition, self::integer($data['payload'], 'occurredVersion'));

            if ($eventId->value !== self::string($data, 'eventId')
                || $eventId->value !== self::string($data['payload'], 'eventId')
                || self::string($data['payload'], 'aggregateType') !== 'MediaItemLifecycle') {
                throw new MediaItemLifecycleEventTransportException('Media item lifecycle event identity is inconsistent.');
            }

            $restored = new self(new MediaItemLifecycleEvent(
                new MediaItemLifecycleEventMetadata(
                    MediaItemLifecycleEventType::from(self::string($data['metadata'], 'eventType')),
                    MediaItemLifecycleEventPayloadVersion::from(self::integer($data['metadata'], 'payloadVersion')),
                    MediaItemLifecycleActorId::fromString(self::string($data['metadata'], 'actorId')),
                    MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable(self::string($data['metadata'], 'occurredAt'))),
                    MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable(self::string($data['metadata'], 'recordedAt'))),
                ),
                new MediaItemLifecycleEventPayload(
                    $eventId,
                    $mediaId,
                    self::string($data['payload'], 'transition'),
                    $previousState,
                    $currentState,
                    $action,
                    self::integer($data['payload'], 'version'),
                    self::integer($data['payload'], 'occurredVersion'),
                ),
            ));

            if ($restored->canonicalEvent !== $fields['canonicalEvent']) {
                throw new MediaItemLifecycleEventTransportException('Media item lifecycle event serialization is not canonical.');
            }

            return $restored;
        } catch (MediaItemLifecycleEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new MediaItemLifecycleEventTransportException('Media item lifecycle delivery payload cannot be restored.', previous: $error);
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new MediaItemLifecycleEventTransportException("Media item lifecycle event field {$field} must be a string.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new MediaItemLifecycleEventTransportException("Media item lifecycle event field {$field} must be an integer.");
        }

        return $data[$field];
    }
}
