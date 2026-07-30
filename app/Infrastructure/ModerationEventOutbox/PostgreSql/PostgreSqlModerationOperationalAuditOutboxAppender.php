<?php

namespace App\Infrastructure\ModerationEventOutbox\PostgreSql;

use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventRouting\ModerationOperationalAuditDestinationMatrix;
use App\Application\ModerationOperationalAuditEventProduction\Contract\ModerationOperationalAuditOutboxAppenderV1;
use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;
use PDO;
use Throwable;

final readonly class PostgreSqlModerationOperationalAuditOutboxAppender implements ModerationOperationalAuditOutboxAppenderV1
{
    public function __construct(
        private PDO $connection,
        private ModerationOperationalAuditDestinationMatrix $destinations = new ModerationOperationalAuditDestinationMatrix,
    ) {}

    public function append(
        ModerationOperationalAuditOutboxMessageV1 $message,
    ): ModerationOutboxAppendResult {
        try {
            $transport = json_encode(
                $message->fields(),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
            $statement = $this->connection->prepare(
                'INSERT INTO moderation_reports.outbox_messages(
                    message_id,event_id,event_type,case_id,aggregate_version,
                    canonical_transport,payload_checksum,correlation_id,causation_id,
                    occurred_at,recorded_at
                 ) VALUES (
                    :message_id,CAST(:event_id AS uuid),:event_type,CAST(:case_id AS uuid),
                    :aggregate_version,CAST(:transport AS jsonb),:checksum,
                    CAST(:correlation_id AS uuid),CAST(:causation_id AS uuid),
                    CAST(:occurred_at AS timestamptz),CAST(:recorded_at AS timestamptz)
                 ) ON CONFLICT DO NOTHING',
            );
            $statement->execute([
                'message_id' => $message->messageId,
                'event_id' => $message->eventId(),
                'event_type' => $message->eventType(),
                'case_id' => $message->caseId(),
                'aggregate_version' => $message->aggregateVersion(),
                'transport' => $transport,
                'checksum' => $message->checksum(),
                'correlation_id' => $message->correlationId(),
                'causation_id' => $message->causationId(),
                'occurred_at' => $message->occurredAt()->format('Y-m-d H:i:s.uP'),
                'recorded_at' => $message->recordedAt()->format('Y-m-d H:i:s.uP'),
            ]);
            $created = $statement->rowCount() === 1;
            if (! $created && ! $this->matches($message)) {
                return ModerationOutboxAppendResult::DivergentMessage;
            }
            $delivery = $this->connection->prepare(
                "INSERT INTO moderation_reports.outbox_deliveries(
                    message_id,destination,status,attempts
                 ) VALUES (:message_id,:destination,'Pending',0)
                 ON CONFLICT(message_id,destination) DO NOTHING",
            );
            foreach ($this->destinations->destinations($message) as $destination) {
                $delivery->execute([
                    'message_id' => $message->messageId,
                    'destination' => $destination->value,
                ]);
            }

            return $created
                ? ModerationOutboxAppendResult::Stored
                : ModerationOutboxAppendResult::AlreadyStored;
        } catch (Throwable) {
            return ModerationOutboxAppendResult::Rejected;
        }
    }

    private function matches(ModerationOperationalAuditOutboxMessageV1 $message): bool
    {
        $statement = $this->connection->prepare(
            'SELECT message_id,event_id::text,payload_checksum
             FROM moderation_reports.outbox_messages
             WHERE message_id=:message_id OR event_id=CAST(:event_id AS uuid)',
        );
        $statement->execute([
            'message_id' => $message->messageId,
            'event_id' => $message->eventId(),
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row)
            && hash_equals((string) $row['message_id'], $message->messageId)
            && hash_equals((string) $row['event_id'], $message->eventId())
            && hash_equals((string) $row['payload_checksum'], $message->checksum());
    }
}
