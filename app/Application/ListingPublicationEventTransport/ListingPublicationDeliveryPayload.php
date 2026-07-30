<?php

namespace App\Application\ListingPublicationEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventId;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventPayload;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventPayloadVersion;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventSerializer;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventType;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Throwable;

final readonly class ListingPublicationDeliveryPayload implements PublicProjectionDeliveryPayload
{
    private string $canonicalEvent;

    public function __construct(public ListingPublicationEvent $event)
    {
        $this->canonicalEvent = (new ListingPublicationEventSerializer)->serialize($event);
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
            throw new ListingPublicationEventTransportException('Listing publication delivery payload envelope is invalid.');
        }
        try {
            $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata']
                || ! is_array($data['payload'])
                || ! is_array($data['metadata'])
                || array_keys($data['payload']) !== ['listingId', 'previousState', 'state', 'action', 'publicationVersion']
                || array_keys($data['metadata']) !== ['occurredAt', 'recordedAt']) {
                throw new ListingPublicationEventTransportException('Canonical listing publication event shape is invalid.');
            }
            $type = ListingPublicationEventType::from(self::string($data, 'eventType'));
            $version = ListingPublicationEventPayloadVersion::from(self::integer($data, 'payloadVersion'));
            $payload = new ListingPublicationEventPayload(
                ListingId::fromString(self::string($data['payload'], 'listingId')),
                ListingPublicationState::from(self::string($data['payload'], 'previousState')),
                ListingPublicationState::from(self::string($data['payload'], 'state')),
                ListingPublicationAction::from(self::string($data['payload'], 'action')),
                self::integer($data['payload'], 'publicationVersion'),
            );
            $metadata = new ListingPublicationEventMetadata(
                ListingPublicationEventInstant::fromCanonicalUtc(self::string($data['metadata'], 'occurredAt')),
                ListingPublicationEventInstant::fromCanonicalUtc(self::string($data['metadata'], 'recordedAt')),
            );
            $eventId = ListingPublicationEventId::derive($type, $version, $payload);
            if ($eventId->value !== self::string($data, 'eventId')) {
                throw new ListingPublicationEventTransportException('Listing publication event identity is inconsistent.');
            }
            $restored = new self(new ListingPublicationEvent($eventId, $type, $version, $payload, $metadata));
            if ($restored->canonicalEvent !== $fields['canonicalEvent']) {
                throw new ListingPublicationEventTransportException('Listing publication event serialization is not canonical.');
            }

            return $restored;
        } catch (ListingPublicationEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new ListingPublicationEventTransportException('Listing publication delivery payload cannot be restored.', previous: $error);
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new ListingPublicationEventTransportException("Listing publication event field {$field} must be a string.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new ListingPublicationEventTransportException("Listing publication event field {$field} must be an integer.");
        }

        return $data[$field];
    }
}
