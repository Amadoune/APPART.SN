<?php

namespace App\Infrastructure\PublicProjectionOutbox\PostgreSql;

use App\Application\AccountStatusEventTransport\AccountStatusDeliveryPayload;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
use App\Application\ListingPublicationEventTransport\ListingPublicationDeliveryPayload;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleDeliveryPayload;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleDeliveryPayload;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryContentSeoPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryMediaPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryPropertyPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliverySearchPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryDestination;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryRoutingProofV1;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryTraceId;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxAttemptCount;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimState;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxLease;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineReason;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineRecord;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryBackoff;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventType;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventType;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventType;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventType;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventType;
use DateTimeImmutable;
use JsonException;
use UnexpectedValueException;

final readonly class PostgreSqlPublicProjectionOutboxMapper
{
    /** @return array<string, bool|int|string|null> */
    public function messageParameters(PublicProjectionDeliveryMessage $message): array
    {
        try {
            $payload = json_encode($message->payload->fields(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        } catch (JsonException $error) {
            throw new UnexpectedValueException('Delivery payload cannot be mapped.', previous: $error);
        }

        return [
            'message_id' => $message->messageId->value, 'idempotency_key' => $message->idempotencyKey->value,
            'source_module' => $message->sourceModule->value, 'aggregate_type' => $message->aggregateType->value,
            'aggregate_id' => $message->aggregateId->value, 'aggregate_version' => $message->order->aggregateVersion,
            'event_index' => $message->order->eventIndex->value, 'event_type' => $message->eventType->value,
            'payload_version' => $message->payloadVersion->value, 'payload' => $payload,
            'payload_checksum' => $message->payload->checksum(), 'occurred_at' => $message->occurredAt->format('Y-m-d H:i:s.uP'),
            'recorded_at' => $message->recordedAt->format('Y-m-d H:i:s.uP'), 'correlation_id' => $message->correlationId?->value,
            'causation_id' => $message->causationId?->value,
        ];
    }

    /** @return array<string, int|string> */
    public function routingParameters(PublicProjectionRoutedDeliveryMessageV1 $delivery): array
    {
        return [
            'routing_destination' => $delivery->destination->value,
            'routing_version' => $delivery->routingProof->routingVersion,
            'routing_checksum' => $delivery->routingProof->checksum,
        ];
    }

    /** @param array<string, mixed> $row */
    public function toRecord(array $row): PublicProjectionOutboxRecord
    {
        $module = PublicProjectionDeliverySourceModule::fromString((string) $row['source_module']);
        $aggregateType = PublicProjectionDeliveryAggregateType::fromString((string) $row['aggregate_type']);
        $aggregateId = PublicProjectionDeliveryAggregateId::fromString((string) $row['aggregate_id']);
        $eventType = PublicProjectionDeliveryEventType::fromString((string) $row['event_type']);
        $payloadVersion = PublicProjectionDeliveryPayloadVersion::fromInt((int) $row['payload_version']);
        $eventIndex = PublicProjectionDeliveryEventIndex::fromInt((int) $row['event_index']);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($module, $aggregateType, $aggregateId, (int) $row['aggregate_version'], $eventIndex, $eventType, $payloadVersion);
        if ($key->value !== $row['idempotency_key']) {
            throw new UnexpectedValueException('Persisted Outbox idempotency key is inconsistent.');
        }
        $payload = $this->payload($eventType->value, (string) $row['payload']);
        if ($payload->checksum() !== $row['payload_checksum']) {
            throw new UnexpectedValueException('Persisted Outbox payload checksum is inconsistent.');
        }
        $message = new PublicProjectionDeliveryMessage(
            PublicProjectionDeliveryMessageId::fromString((string) $row['message_id']), $key, $eventType, $payloadVersion,
            $module, $aggregateType, $aggregateId, new PublicProjectionDeliveryOrder((int) $row['aggregate_version'], $eventIndex),
            new DateTimeImmutable((string) $row['occurred_at']), new DateTimeImmutable((string) $row['recorded_at']), $payload,
            $row['correlation_id'] === null ? null : PublicProjectionDeliveryTraceId::fromString((string) $row['correlation_id']),
            $row['causation_id'] === null ? null : PublicProjectionDeliveryTraceId::fromString((string) $row['causation_id']),
        );
        $lease = $row['claim_owner_id'] === null ? null : new PublicProjectionOutboxLease(PublicProjectionOutboxClaimOwnerId::fromString((string) $row['claim_owner_id']), new DateTimeImmutable((string) $row['claimed_at']), new DateTimeImmutable((string) $row['claimed_until']));
        $retry = $row['retry_classification'] === null ? null : new PublicProjectionOutboxRetryDecision(PublicProjectionOutboxRetryClassification::from((string) $row['retry_classification']), new PublicProjectionOutboxRetryBackoff((int) $row['retry_delay_seconds']), (bool) $row['retry_allowed']);
        $attempts = PublicProjectionOutboxAttemptCount::fromInt((int) $row['attempts']);
        $consumer = PublicProjectionOutboxConsumerId::fromString((string) $row['consumer_id']);
        $quarantine = $row['quarantine_reason'] === null ? null : new PublicProjectionOutboxQuarantineRecord($message->messageId, $consumer, new PublicProjectionOutboxQuarantineDecision(PublicProjectionOutboxQuarantineReason::from((string) $row['quarantine_reason']), (string) $row['last_error_code']), $attempts);

        $routedDelivery = $this->routedDelivery($row, $message);

        return new PublicProjectionOutboxRecord($message, $consumer, PublicProjectionDeliveryStatus::from((string) $row['status']), $attempts, PublicProjectionOutboxClaimState::from((string) $row['claim_state']), $lease, $retry, $quarantine, $routedDelivery);
    }

    private function payload(string $eventType, string $json): PublicProjectionDeliveryPayload
    {
        try {
            $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new UnexpectedValueException('Persisted Outbox payload is invalid JSON.', previous: $error);
        }
        if (! is_array($data)) {
            throw new UnexpectedValueException('Persisted Outbox payload is not an object.');
        }
        if (ListingPublicationEventType::tryFrom($eventType) !== null) {
            return ListingPublicationDeliveryPayload::restore($data);
        }
        if (PropertyLifecycleEventType::tryFrom($eventType) !== null) {
            return PropertyLifecycleDeliveryPayload::restore($data);
        }
        if (ReservationLifecycleEventType::tryFrom($eventType) !== null) {
            return ReservationLifecycleDeliveryPayload::restore($data);
        }
        if (LeadLifecycleEventType::tryFrom($eventType) !== null) {
            return LeadLifecycleDeliveryPayload::restore($data);
        }
        if (ProfessionalStatusEventType::tryFrom($eventType) !== null) {
            return ProfessionalStatusDeliveryPayload::restore($data);
        }
        if (MediaItemLifecycleEventType::tryFrom($eventType) !== null) {
            return MediaItemLifecycleDeliveryPayload::restore($data);
        }
        if (AdministrativeActionLifecycleEventType::tryFrom($eventType) !== null) {
            return AdministrativeActionLifecycleDeliveryPayload::restore($data);
        }
        if (PlaceLifecycleEventType::tryFrom($eventType) !== null) {
            return PlaceLifecycleDeliveryPayload::restore($data);
        }
        if (AccountStatusEventType::tryFrom($eventType) !== null) {
            return AccountStatusDeliveryPayload::restore($data);
        }

        return match ($eventType) {
            'listing.reconstruction.requested' => new PublicProjectionDeliveryListingPayload($this->field($data, 'listingId')),
            'property.reconstruction.requested' => new PublicProjectionDeliveryPropertyPayload($this->field($data, 'propertyId')),
            'media.reconstruction.requested' => new PublicProjectionDeliveryMediaPayload($this->field($data, 'mediaCollectionId')),
            'search.reconstruction.requested' => new PublicProjectionDeliverySearchPayload($this->field($data, 'listingId')),
            'content_seo.reconstruction.requested' => new PublicProjectionDeliveryContentSeoPayload($this->field($data, 'listingId')),
            default => throw new UnexpectedValueException('Persisted Outbox event type is unsupported.'),
        };
    }

    /** @param array<string, mixed> $row */
    private function routedDelivery(
        array $row,
        PublicProjectionDeliveryMessage $message,
    ): ?PublicProjectionRoutedDeliveryMessageV1 {
        $fields = ['routing_destination', 'routing_version', 'routing_checksum'];
        $present = array_map(static fn (string $field): bool => array_key_exists($field, $row), $fields);
        if (! in_array(true, $present, true)) {
            return null;
        }

        $values = array_map(static fn (string $field): mixed => $row[$field] ?? null, $fields);
        if (count(array_filter($values, static fn (mixed $value): bool => $value !== null)) === 0) {
            return null;
        }
        if (in_array(null, $values, true)) {
            throw new UnexpectedValueException('Persisted routed delivery proof is incomplete.');
        }

        $destination = PublicProjectionDeliveryDestination::fromString((string) $row['routing_destination']);
        $proof = new PublicProjectionDeliveryRoutingProofV1(
            $message->messageId,
            $message->sourceModule,
            $message->eventType,
            $destination,
            (int) $row['routing_version'],
            (string) $row['routing_checksum'],
        );

        return new PublicProjectionRoutedDeliveryMessageV1($message, $destination, $proof);
    }

    /** @param array<mixed> $data */
    private function field(array $data, string $name): string
    {
        if (array_keys($data) !== [$name] || ! is_string($data[$name]) || $data[$name] === '') {
            throw new UnexpectedValueException('Persisted Outbox payload schema is invalid.');
        }

        return $data[$name];
    }
}
