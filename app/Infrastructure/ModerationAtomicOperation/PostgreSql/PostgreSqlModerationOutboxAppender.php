<?php

namespace App\Infrastructure\ModerationAtomicOperation\PostgreSql;

use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use DateTimeImmutable;
use PDO;
use PDOException;

final readonly class PostgreSqlModerationOutboxAppender implements ModerationOutboxAppenderV1
{
    public function __construct(
        private PDO $connection,
        private ModerationEventTransportSerializer $serializer,
    ) {}

    public function append(ModerationDeliveryMessageV1 $message): ModerationOutboxAppendResult
    {
        try {
            $lock = $this->connection->prepare(
                'SELECT pg_advisory_xact_lock(hashtextextended(:message_id, 0))',
            );
            $lock->execute(['message_id' => $message->messageId]);

            $transport = $this->serializer->serialize($message);
            $insert = $this->connection->prepare(
                'INSERT INTO moderation_reports.atomic_outbox_appends(
                    message_id,event_id,payload_checksum,canonical_transport,
                    correlation_id,causation_id,recorded_at
                 ) VALUES (
                    :message_id,CAST(:event_id AS uuid),:payload_checksum,
                    CAST(:canonical_transport AS jsonb),CAST(:correlation_id AS uuid),
                    CAST(:causation_id AS uuid),CAST(:recorded_at AS timestamptz)
                 ) ON CONFLICT DO NOTHING',
            );
            $insert->execute([
                'message_id' => $message->messageId,
                'event_id' => $message->event->eventId,
                'payload_checksum' => $message->event->checksum,
                'canonical_transport' => $transport,
                'correlation_id' => $message->event->correlationId,
                'causation_id' => $message->event->causationId,
                'recorded_at' => (new DateTimeImmutable)->format('Y-m-d H:i:s.uP'),
            ]);

            if ($insert->rowCount() === 1) {
                return ModerationOutboxAppendResult::Stored;
            }

            $read = $this->connection->prepare(
                'SELECT message_id,event_id::text,payload_checksum,canonical_transport::text
                 FROM moderation_reports.atomic_outbox_appends
                 WHERE message_id=:message_id OR event_id=CAST(:event_id AS uuid)',
            );
            $read->execute([
                'message_id' => $message->messageId,
                'event_id' => $message->event->eventId,
            ]);
            $row = $read->fetch(PDO::FETCH_ASSOC);

            if (
                is_array($row)
                && hash_equals((string) $row['message_id'], $message->messageId)
                && hash_equals((string) $row['event_id'], $message->event->eventId)
                && hash_equals((string) $row['payload_checksum'], $message->event->checksum)
            ) {
                return ModerationOutboxAppendResult::AlreadyStored;
            }

            return ModerationOutboxAppendResult::DivergentMessage;
        } catch (PDOException) {
            return ModerationOutboxAppendResult::Rejected;
        }
    }
}
