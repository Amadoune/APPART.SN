<?php

namespace App\Application\AdministrativeActionLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEvent;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventMetadata;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayloadVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventSerializer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;
use Throwable;

final readonly class AdministrativeActionLifecycleDeliveryPayload implements PublicProjectionDeliveryPayload
{
    private string $canonicalEvent;

    public function __construct(public AdministrativeActionLifecycleEvent $event)
    {
        $this->canonicalEvent = (new AdministrativeActionLifecycleEventSerializer)->serialize($event);
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
            throw new AdministrativeActionLifecycleEventTransportException('Administrative Action Lifecycle delivery payload is invalid.');
        }

        try {
            $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || array_keys($data) !== ['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata', 'checksum']
                || ! is_array($data['payload'])
                || ! is_array($data['metadata'])
                || array_keys($data['payload']) !== ['eventId', 'aggregateType', 'administrativeActionId', 'transition', 'previousState', 'currentState', 'action', 'version', 'occurredVersion']
                || array_keys($data['metadata']) !== ['eventType', 'payloadVersion', 'actorId', 'occurredAt', 'recordedAt']) {
                throw new AdministrativeActionLifecycleEventTransportException('Canonical Administrative Action Lifecycle event shape is invalid.');
            }

            $type = AdministrativeActionLifecycleEventType::from(self::string($data, 'eventType'));
            $version = AdministrativeActionLifecycleEventPayloadVersion::from(self::integer($data, 'payloadVersion'));
            $actionId = AdministrativeActionId::fromString(self::string($data['payload'], 'administrativeActionId'));
            $previous = AdministrativeActionLifecycleState::from(self::string($data['payload'], 'previousState'));
            $current = AdministrativeActionLifecycleState::from(self::string($data['payload'], 'currentState'));
            $action = AdministrativeActionLifecycleAction::from(self::string($data['payload'], 'action'));
            $transition = new AdministrativeActionLifecycleTransition($previous, $current, $action);
            $eventId = AdministrativeActionLifecycleEventId::derive(
                $type,
                $version,
                $actionId,
                $transition,
                self::integer($data['payload'], 'occurredVersion'),
            );

            if ($eventId->value !== self::string($data, 'eventId')
                || $eventId->value !== self::string($data['payload'], 'eventId')
                || self::string($data['payload'], 'aggregateType') !== 'AdministrativeActionLifecycle') {
                throw new AdministrativeActionLifecycleEventTransportException('Administrative Action Lifecycle event identity is inconsistent.');
            }

            $restored = new self(new AdministrativeActionLifecycleEvent(
                new AdministrativeActionLifecycleEventMetadata(
                    AdministrativeActionLifecycleEventType::from(self::string($data['metadata'], 'eventType')),
                    AdministrativeActionLifecycleEventPayloadVersion::from(self::integer($data['metadata'], 'payloadVersion')),
                    ActorId::fromString(self::string($data['metadata'], 'actorId')),
                    AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable(self::string($data['metadata'], 'occurredAt'))),
                    AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable(self::string($data['metadata'], 'recordedAt'))),
                ),
                new AdministrativeActionLifecycleEventPayload(
                    $eventId,
                    $actionId,
                    self::string($data['payload'], 'transition'),
                    $previous,
                    $current,
                    $action,
                    self::integer($data['payload'], 'version'),
                    self::integer($data['payload'], 'occurredVersion'),
                ),
            ));

            if ($restored->event->checksum()->value !== self::string($data, 'checksum')
                || $restored->canonicalEvent !== $fields['canonicalEvent']) {
                throw new AdministrativeActionLifecycleEventTransportException('Administrative Action Lifecycle event is not canonical.');
            }

            return $restored;
        } catch (AdministrativeActionLifecycleEventTransportException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new AdministrativeActionLifecycleEventTransportException('Administrative Action Lifecycle event cannot be restored.', previous: $error);
        }
    }

    /** @param array<mixed> $data */
    private static function string(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new AdministrativeActionLifecycleEventTransportException("Administrative Action Lifecycle event field {$field} must be a string.");
        }

        return $data[$field];
    }

    /** @param array<mixed> $data */
    private static function integer(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new AdministrativeActionLifecycleEventTransportException("Administrative Action Lifecycle event field {$field} must be an integer.");
        }

        return $data[$field];
    }
}
