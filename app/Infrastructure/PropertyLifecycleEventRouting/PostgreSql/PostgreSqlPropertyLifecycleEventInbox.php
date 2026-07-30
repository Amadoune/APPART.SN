<?php

namespace App\Infrastructure\PropertyLifecycleEventRouting\PostgreSql;

use App\Application\PropertyLifecycleEventRouting\Contract\PropertyLifecycleEventDestination;
use App\Application\PropertyLifecycleEventRouting\PropertyLifecycleEventDestinationResult;
use App\Application\PropertyLifecycleEventRouting\PropertyLifecycleEventDestinationStatus;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventSerializer;
use PDO;
use PDOException;

final readonly class PostgreSqlPropertyLifecycleEventInbox implements PropertyLifecycleEventDestination
{
    public function __construct(private PDO $connection, private PropertyLifecycleEventSerializer $serializer) {}

    public function transfer(PropertyLifecycleEvent $event): PropertyLifecycleEventDestinationResult
    {
        $canonical = $this->serializer->serialize($event);
        $checksum = hash('sha256', $canonical);
        $inboxId = 'plei:'.hash('sha256', $event->eventId->value);
        try {
            $statement = $this->connection->prepare("INSERT INTO real_estate_catalog.property_lifecycle_event_inbox (inbox_id,event_id,event_type,payload_version,canonical_event,event_checksum,status,delivery_attempts) VALUES (:inbox_id,:event_id,:event_type,:payload_version,:canonical_event,:event_checksum,'pending',0) ON CONFLICT (event_id) DO NOTHING");
            $statement->execute([
                'inbox_id' => $inboxId,
                'event_id' => $event->eventId->value,
                'event_type' => $event->type->value,
                'payload_version' => $event->payloadVersion->value,
                'canonical_event' => $canonical,
                'event_checksum' => $checksum,
            ]);
            if ($statement->rowCount() === 1) {
                return new PropertyLifecycleEventDestinationResult(PropertyLifecycleEventDestinationStatus::Stored);
            }
            $existing = $this->connection->prepare('SELECT inbox_id,event_type,payload_version,canonical_event,event_checksum,status FROM real_estate_catalog.property_lifecycle_event_inbox WHERE event_id=:event_id');
            $existing->execute(['event_id' => $event->eventId->value]);
            $row = $existing->fetch(PDO::FETCH_ASSOC);
            $identical = is_array($row)
                && $row['inbox_id'] === $inboxId
                && $row['event_type'] === $event->type->value
                && (int) $row['payload_version'] === $event->payloadVersion->value
                && $row['canonical_event'] === $canonical
                && $row['event_checksum'] === $checksum;

            return new PropertyLifecycleEventDestinationResult($identical ? PropertyLifecycleEventDestinationStatus::AlreadyStored : PropertyLifecycleEventDestinationStatus::Rejected);
        } catch (PDOException $error) {
            $state = $error->getCode();
            if (str_starts_with($state, '08') || in_array($state, ['57P01', '57P02', '57P03'], true)) {
                return new PropertyLifecycleEventDestinationResult(PropertyLifecycleEventDestinationStatus::Unavailable);
            }
            if (str_starts_with($state, '22') || str_starts_with($state, '23')) {
                return new PropertyLifecycleEventDestinationResult(PropertyLifecycleEventDestinationStatus::Rejected);
            }

            return new PropertyLifecycleEventDestinationResult(PropertyLifecycleEventDestinationStatus::RetryableFailure);
        }
    }
}
