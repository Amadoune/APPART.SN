<?php

namespace App\Infrastructure\ModerationEventOutbox\PostgreSql;

use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationOperationalAudit\Contract\ModerationOperationalAuditDeliveryReaderV1;
use App\Application\ModerationOperationalAudit\CorruptedOperationalAuditDelivery;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditDelivery;
use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;
use App\Application\ModerationResidualOperationalAuditContract\ResidualOperationalAuditRuntimeCatalogV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\FindingRecordedEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\ModerationOperationalAuditEventTypeV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\QueueItemClaimedEventV1;
use DateTimeImmutable;
use InvalidArgumentException;
use PDO;
use Throwable;

final readonly class PostgreSqlModerationOperationalAuditDeliveryReader implements ModerationOperationalAuditDeliveryReaderV1
{
    public function __construct(
        private PDO $connection,
        private ResidualOperationalAuditRuntimeCatalogV1 $catalog = new ResidualOperationalAuditRuntimeCatalogV1,
    ) {}

    public function claimNext(string $owner, DateTimeImmutable $now): ?ModerationOperationalAuditDelivery
    {
        if (preg_match('/^[a-zA-Z0-9._-]{1,96}$/', $owner) !== 1) {
            throw new InvalidArgumentException('Invalid operational Audit claim owner.');
        }

        return $this->transaction(function () use ($owner, $now): ?ModerationOperationalAuditDelivery {
            $statement = $this->connection->prepare(
                "SELECT d.message_id,d.attempts,m.canonical_transport::text
                 FROM moderation_reports.outbox_deliveries d
                 JOIN moderation_reports.outbox_messages m USING(message_id)
                 WHERE d.destination=:destination
                   AND (d.status='Pending'
                    OR (d.status='Retry' AND d.available_at<=CAST(:now AS timestamptz))
                    OR (d.status='Claimed' AND d.claimed_until<=CAST(:now AS timestamptz)))
                 ORDER BY d.available_at,d.message_id
                 FOR UPDATE OF d SKIP LOCKED LIMIT 1",
            );
            $statement->execute([
                'destination' => ModerationRoutingDestination::DeliveryObservation->value,
                'now' => $now->format('Y-m-d H:i:s.uP'),
            ]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if (! is_array($row)) {
                return null;
            }
            $claim = $this->connection->prepare(
                "UPDATE moderation_reports.outbox_deliveries
                 SET status='Claimed',attempts=attempts+1,claim_owner=:owner,
                     claimed_until=CAST(:until AS timestamptz)
                 WHERE message_id=:message_id AND destination=:destination",
            );
            $claim->execute([
                'owner' => $owner,
                'until' => $now->modify('+30 seconds')->format('Y-m-d H:i:s.uP'),
                'message_id' => $row['message_id'],
                'destination' => ModerationRoutingDestination::DeliveryObservation->value,
            ]);

            try {
                $message = $this->restore((string) $row['canonical_transport']);
            } catch (Throwable $error) {
                $quarantine = $this->connection->prepare(
                    "UPDATE moderation_reports.outbox_deliveries
                     SET status='Quarantined',claim_owner=NULL,claimed_until=NULL,
                         last_error_code='corrupted_audit_source'
                     WHERE message_id=:message_id AND destination=:destination
                       AND status='Claimed' AND claim_owner=:owner",
                );
                $quarantine->execute([
                    'message_id' => $row['message_id'],
                    'destination' => ModerationRoutingDestination::DeliveryObservation->value,
                    'owner' => $owner,
                ]);
                throw new CorruptedOperationalAuditDelivery(
                    'Operational Audit delivery was quarantined.',
                    previous: $error,
                );
            }

            return new ModerationOperationalAuditDelivery(
                $message,
                ((int) $row['attempts']) + 1,
                $owner,
            );
        });
    }

    public function retry(
        ModerationOperationalAuditDelivery $delivery,
        DateTimeImmutable $availableAt,
        string $errorCode,
    ): bool {
        return $this->claimedUpdate(
            $delivery,
            "status='Retry',claim_owner=NULL,claimed_until=NULL,available_at=CAST(:value AS timestamptz),last_error_code=:error",
            $availableAt->format('Y-m-d H:i:s.uP'),
            $errorCode,
        );
    }

    public function markDelivered(
        ModerationOperationalAuditDelivery $delivery,
        DateTimeImmutable $at,
    ): bool {
        return $this->claimedUpdate(
            $delivery,
            "status='Delivered',claim_owner=NULL,claimed_until=NULL,delivered_at=CAST(:value AS timestamptz),last_error_code=NULL",
            $at->format('Y-m-d H:i:s.uP'),
        );
    }

    public function quarantine(
        ModerationOperationalAuditDelivery $delivery,
        string $errorCode,
    ): bool {
        return $this->claimedUpdate(
            $delivery,
            "status='Quarantined',claim_owner=NULL,claimed_until=NULL,last_error_code=:error",
            '',
            $errorCode,
        );
    }

    private function restore(string $transport): ModerationOperationalAuditOutboxMessageV1
    {
        $fields = json_decode($transport, true, flags: JSON_THROW_ON_ERROR);
        $contract = is_array($fields) ? ($fields['payload'] ?? null) : null;
        $payload = is_array($contract) ? ($contract['payload'] ?? null) : null;
        if (! is_array($fields) || ! is_array($contract) || ! is_array($payload)) {
            throw new InvalidArgumentException('Invalid operational Audit transport.');
        }
        $type = (string) ($contract['eventType'] ?? '');
        if (! $this->catalog->accepts($type)) {
            throw new InvalidArgumentException('Unsupported operational Audit Event V1.');
        }
        $event = match ($type) {
            ModerationEventTypeV1::ReportSubmitted->value,
            ModerationEventTypeV1::ReportValidated->value,
            ModerationEventTypeV1::DecisionIssued->value,
            ModerationEventTypeV1::CaseClosed->value,
            ModerationEventTypeV1::TargetActionCompleted->value => new ModerationEventV1(
                ModerationEventTypeV1::from($type),
                (string) $contract['caseId'],
                (int) $contract['aggregateVersion'],
                $payload,
                (string) $contract['policyVersion'],
                new DateTimeImmutable((string) $contract['occurredAt']),
                new DateTimeImmutable((string) $contract['recordedAt']),
                (string) $contract['correlationId'],
                (string) $contract['causationId'],
            ),
            ModerationOperationalAuditEventTypeV1::FindingRecorded->value => new FindingRecordedEventV1(
                (string) $contract['caseId'],
                (string) ($payload['findingId'] ?? ''),
                (int) $contract['aggregateVersion'],
                (string) $contract['policyVersion'],
                new DateTimeImmutable((string) $contract['occurredAt']),
                new DateTimeImmutable((string) $contract['recordedAt']),
                (string) $contract['correlationId'],
                (string) $contract['causationId'],
            ),
            ModerationOperationalAuditEventTypeV1::QueueItemClaimed->value => new QueueItemClaimedEventV1(
                (string) $contract['caseId'],
                (string) ($payload['queueItemId'] ?? ''),
                (int) $contract['aggregateVersion'],
                (string) $contract['policyVersion'],
                new DateTimeImmutable((string) $contract['occurredAt']),
                new DateTimeImmutable((string) $contract['recordedAt']),
                (string) $contract['correlationId'],
                (string) $contract['causationId'],
            ),
            default => throw new InvalidArgumentException('Unsupported operational Audit Event V1.'),
        };
        $message = new ModerationOperationalAuditOutboxMessageV1($event);
        if (
            ! hash_equals((string) ($fields['messageId'] ?? ''), $message->messageId)
            || ! hash_equals((string) ($contract['eventId'] ?? ''), $message->eventId())
            || ! hash_equals((string) ($contract['checksum'] ?? ''), $message->checksum())
        ) {
            throw new InvalidArgumentException('Corrupted operational Audit transport.');
        }

        return $message;
    }

    private function claimedUpdate(
        ModerationOperationalAuditDelivery $delivery,
        string $set,
        string $value,
        ?string $error = null,
    ): bool {
        $statement = $this->connection->prepare(
            "UPDATE moderation_reports.outbox_deliveries SET {$set}
             WHERE message_id=:message_id AND destination=:destination
               AND status='Claimed' AND claim_owner=:owner",
        );
        $parameters = [
            'message_id' => $delivery->message->messageId,
            'destination' => ModerationRoutingDestination::DeliveryObservation->value,
            'owner' => $delivery->claimOwner,
        ];
        if (str_contains($set, ':value')) {
            $parameters['value'] = $value;
        }
        if (str_contains($set, ':error')) {
            $parameters['error'] = $error;
        }
        $statement->execute($parameters);

        return $statement->rowCount() === 1;
    }

    /** @template T
     * @param  callable(): T  $operation
     * @return T
     */
    private function transaction(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'moderation_operational_audit_delivery';
        $owner
            ? $this->connection->beginTransaction()
            : $this->connection->exec("SAVEPOINT {$savepoint}");
        try {
            $result = $operation();
            $owner
                ? $this->connection->commit()
                : $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");

            return $result;
        } catch (Throwable $error) {
            $owner
                ? $this->connection->rollBack()
                : $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
            throw $error;
        }
    }
}
