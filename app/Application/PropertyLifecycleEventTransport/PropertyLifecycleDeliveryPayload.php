<?php

namespace App\Application\PropertyLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventId;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventMetadata;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventPayload;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventPayloadVersion;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventSerializer;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventType;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Throwable;

final readonly class PropertyLifecycleDeliveryPayload implements PublicProjectionDeliveryPayload
{
    private string $canonicalEvent;

    public function __construct(public PropertyLifecycleEvent $event)
    {
        $this->canonicalEvent = (new PropertyLifecycleEventSerializer)->serialize($event);
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
            throw new PropertyLifecycleEventTransportException('Property lifecycle delivery payload envelope is invalid.');
        }
        try {
            $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata']
                || ! is_array($data['payload'])
                || ! is_array($data['metadata'])
                || array_keys($data['payload']) !== ['propertyId', 'previousState', 'state', 'action', 'lifecycleVersion']
                || array_keys($data['metadata']) !== ['occurredAt', 'recordedAt']) {
                throw new PropertyLifecycleEventTransportException('Canonical property lifecycle event shape is invalid.');
            }
            $type = PropertyLifecycleEventType::from(self::string($data, 'eventType'));
            $version = PropertyLifecycleEventPayloadVersion::from(self::integer($data, 'payloadVersion'));
            $payload = new PropertyLifecycleEventPayload(
                PropertyId::fromString(self::string($data['payload'], 'propertyId')),
                PropertyLifecycleState::from(self::string($data['payload'], 'previousState')),
                PropertyLifecycleState::from(self::string($data['payload'], 'state')),
                PropertyLifecycleAction::from(self::string($data['payload'], 'action')),
                self::integer($data['payload'], 'lifecycleVersion'),
            );
            $metadata = new PropertyLifecycleEventMetadata(
                PropertyLifecycleEventInstant::fromCanonicalUtc(self::string($data['metadata'], 'occurredAt')),
                PropertyLifecycleEventInstant::fromCanonicalUtc(self::string($data['metadata'], 'recordedAt')),
            );
            $eventId = PropertyLifecycleEventId::derive($type, $version, $payload);
            if ($eventId->value !== self::string($data, 'eventId')) {
                throw new PropertyLifecycleEventTransportException('Property lifecycle event identity is inconsistent.');
            }
            $restored = new self(new PropertyLifecycleEvent($eventId, $type, $version, $payload, $metadata));
            if ($restored->canonicalEvent !== $fields['canonicalEvent']) {
                throw new PropertyLifecycleEventTransportException('Property lifecycle event serialization is not canonical.');
            }

            return $restored;
        } catch (PropertyLifecycleEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new PropertyLifecycleEventTransportException('Property lifecycle delivery payload cannot be restored.', previous: $error);
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new PropertyLifecycleEventTransportException("Property lifecycle event field {$field} must be a string.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new PropertyLifecycleEventTransportException("Property lifecycle event field {$field} must be an integer.");
        }

        return $data[$field];
    }
}
