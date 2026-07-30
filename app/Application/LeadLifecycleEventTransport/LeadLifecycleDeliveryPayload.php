<?php

namespace App\Application\LeadLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEvent;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventMetadata;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayloadVersion;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventSerializer;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use Throwable;

final readonly class LeadLifecycleDeliveryPayload implements PublicProjectionDeliveryPayload
{
    private string $canonicalEvent;

    public function __construct(public LeadLifecycleEvent $event)
    {
        $this->canonicalEvent = (new LeadLifecycleEventSerializer)->serialize($event);
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
            throw new LeadLifecycleEventTransportException('Lead lifecycle delivery payload envelope is invalid.');
        }

        try {
            $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata']
                || ! is_array($data['payload'])
                || ! is_array($data['metadata'])
                || array_keys($data['payload']) !== ['eventId', 'aggregateType', 'leadId', 'transition', 'previousState', 'currentState', 'action', 'version', 'occurredVersion']
                || array_keys($data['metadata']) !== ['eventType', 'payloadVersion', 'actorId', 'occurredAt', 'recordedAt']) {
                throw new LeadLifecycleEventTransportException('Canonical Lead lifecycle event shape is invalid.');
            }

            $eventType = LeadLifecycleEventType::from(self::string($data, 'eventType'));
            $payloadVersion = LeadLifecycleEventPayloadVersion::from(self::integer($data, 'payloadVersion'));
            $leadId = LeadId::fromString(self::string($data['payload'], 'leadId'));
            $previousState = LeadLifecycleState::from(self::string($data['payload'], 'previousState'));
            $currentState = LeadLifecycleState::from(self::string($data['payload'], 'currentState'));
            $action = LeadLifecycleAction::from(self::string($data['payload'], 'action'));
            $eventId = LeadLifecycleEventId::derive(
                $eventType,
                $payloadVersion,
                $leadId,
                new LeadLifecycleTransition($previousState, $currentState, $action),
                self::integer($data['payload'], 'occurredVersion'),
            );

            if ($eventId->value !== self::string($data, 'eventId')
                || $eventId->value !== self::string($data['payload'], 'eventId')
                || self::string($data['payload'], 'aggregateType') !== 'LeadLifecycle') {
                throw new LeadLifecycleEventTransportException('Lead lifecycle event identity is inconsistent.');
            }

            $restored = new self(new LeadLifecycleEvent(
                new LeadLifecycleEventMetadata(
                    LeadLifecycleEventType::from(self::string($data['metadata'], 'eventType')),
                    LeadLifecycleEventPayloadVersion::from(self::integer($data['metadata'], 'payloadVersion')),
                    LeadLifecycleActorId::fromString(self::string($data['metadata'], 'actorId')),
                    LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable(self::string($data['metadata'], 'occurredAt'))),
                    LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable(self::string($data['metadata'], 'recordedAt'))),
                ),
                new LeadLifecycleEventPayload(
                    $eventId,
                    $leadId,
                    self::string($data['payload'], 'transition'),
                    $previousState,
                    $currentState,
                    $action,
                    self::integer($data['payload'], 'version'),
                    self::integer($data['payload'], 'occurredVersion'),
                ),
            ));

            if ($restored->canonicalEvent !== $fields['canonicalEvent']) {
                throw new LeadLifecycleEventTransportException('Lead lifecycle event serialization is not canonical.');
            }

            return $restored;
        } catch (LeadLifecycleEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new LeadLifecycleEventTransportException('Lead lifecycle delivery payload cannot be restored.', previous: $error);
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new LeadLifecycleEventTransportException("Lead lifecycle event field {$field} must be a string.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new LeadLifecycleEventTransportException("Lead lifecycle event field {$field} must be an integer.");
        }

        return $data[$field];
    }
}
