<?php

namespace App\Application\ProfessionalStatusEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEvent;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventMetadata;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayloadVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventSerializer;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use Throwable;

final readonly class ProfessionalStatusDeliveryPayload implements PublicProjectionDeliveryPayload
{
    private string $canonicalEvent;

    public function __construct(public ProfessionalStatusEvent $event)
    {
        $this->canonicalEvent = (new ProfessionalStatusEventSerializer)->serialize($event);
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
            throw new ProfessionalStatusEventTransportException('Professional status delivery payload envelope is invalid.');
        }

        try {
            $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata']
                || ! is_array($data['payload'])
                || ! is_array($data['metadata'])
                || array_keys($data['payload']) !== ['eventId', 'aggregateType', 'professionalId', 'transition', 'previousState', 'currentState', 'action', 'version', 'occurredVersion']
                || array_keys($data['metadata']) !== ['eventType', 'payloadVersion', 'actorId', 'occurredAt', 'recordedAt']) {
                throw new ProfessionalStatusEventTransportException('Canonical professional status event shape is invalid.');
            }

            $eventType = ProfessionalStatusEventType::from(self::string($data, 'eventType'));
            $payloadVersion = ProfessionalStatusEventPayloadVersion::from(self::integer($data, 'payloadVersion'));
            $professionalId = ProfessionalStatusId::fromString(self::string($data['payload'], 'professionalId'));
            $previousState = ProfessionalStatusState::from(self::string($data['payload'], 'previousState'));
            $currentState = ProfessionalStatusState::from(self::string($data['payload'], 'currentState'));
            $action = ProfessionalStatusAction::from(self::string($data['payload'], 'action'));
            $transition = new ProfessionalStatusTransition($previousState, $currentState, $action);
            $eventId = ProfessionalStatusEventId::derive($eventType, $payloadVersion, $professionalId, $transition, self::integer($data['payload'], 'occurredVersion'));

            if ($eventId->value !== self::string($data, 'eventId')
                || $eventId->value !== self::string($data['payload'], 'eventId')
                || self::string($data['payload'], 'aggregateType') !== 'ProfessionalStatus') {
                throw new ProfessionalStatusEventTransportException('Professional status event identity is inconsistent.');
            }

            $restored = new self(new ProfessionalStatusEvent(
                new ProfessionalStatusEventMetadata(
                    ProfessionalStatusEventType::from(self::string($data['metadata'], 'eventType')),
                    ProfessionalStatusEventPayloadVersion::from(self::integer($data['metadata'], 'payloadVersion')),
                    ProfessionalStatusActorId::fromString(self::string($data['metadata'], 'actorId')),
                    ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable(self::string($data['metadata'], 'occurredAt'))),
                    ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable(self::string($data['metadata'], 'recordedAt'))),
                ),
                new ProfessionalStatusEventPayload(
                    $eventId,
                    $professionalId,
                    self::string($data['payload'], 'transition'),
                    $previousState,
                    $currentState,
                    $action,
                    self::integer($data['payload'], 'version'),
                    self::integer($data['payload'], 'occurredVersion'),
                ),
            ));

            if ($restored->canonicalEvent !== $fields['canonicalEvent']) {
                throw new ProfessionalStatusEventTransportException('Professional status event serialization is not canonical.');
            }

            return $restored;
        } catch (ProfessionalStatusEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new ProfessionalStatusEventTransportException('Professional status delivery payload cannot be restored.', previous: $error);
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new ProfessionalStatusEventTransportException("Professional status event field {$field} must be a string.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new ProfessionalStatusEventTransportException("Professional status event field {$field} must be an integer.");
        }

        return $data[$field];
    }
}
