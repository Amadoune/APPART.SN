<?php

namespace App\Infrastructure\PublicProjectionOutbox\PostgreSql;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxCursorStore;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxCursor;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxCursorIdentity;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxReplayRequest;
use PDO;

final readonly class PostgreSqlPublicProjectionOutboxCursorStore implements PublicProjectionOutboxCursorStore
{
    public function __construct(private PDO $connection) {}

    public function find(PublicProjectionOutboxCursorIdentity $identity): ?PublicProjectionOutboxCursor
    {
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($identity->sourceModule);
        $statement = $this->connection->prepare("SELECT * FROM {$schema}.public_projection_outbox_cursors WHERE consumer_id=:consumer AND source_module=:module AND aggregate_type=:type AND aggregate_id=:id");
        $statement->execute($this->identity($identity));
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (! is_array($row)) {
            return null;
        }

        return new PublicProjectionOutboxCursor($identity, $this->order($row['progress_version'], $row['progress_index']), $this->order($row['high_watermark_version'], $row['high_watermark_index']), $row['last_message_id'] === null ? null : PublicProjectionDeliveryMessageId::fromString((string) $row['last_message_id']));
    }

    public function advance(PublicProjectionOutboxCursor $cursor): void
    {
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($cursor->identity->sourceModule);
        $statement = $this->connection->prepare("INSERT INTO {$schema}.public_projection_outbox_cursors (consumer_id,source_module,aggregate_type,aggregate_id,progress_version,progress_index,high_watermark_version,high_watermark_index,last_message_id) VALUES (:consumer,:module,:type,:id,:pv,:pi,:hv,:hi,:message) ON CONFLICT (consumer_id,source_module,aggregate_type,aggregate_id) DO UPDATE SET progress_version=EXCLUDED.progress_version,progress_index=EXCLUDED.progress_index,high_watermark_version=EXCLUDED.high_watermark_version,high_watermark_index=EXCLUDED.high_watermark_index,last_message_id=EXCLUDED.last_message_id WHERE (public_projection_outbox_cursors.progress_version,public_projection_outbox_cursors.progress_index) IS NULL OR (public_projection_outbox_cursors.progress_version,public_projection_outbox_cursors.progress_index) <= (EXCLUDED.progress_version,EXCLUDED.progress_index)");
        $statement->execute($this->identity($cursor->identity) + ['pv' => $cursor->progress?->aggregateVersion, 'pi' => $cursor->progress?->eventIndex->value, 'hv' => $cursor->highWatermark?->aggregateVersion, 'hi' => $cursor->highWatermark?->eventIndex->value, 'message' => $cursor->lastMessageId?->value]);
    }

    public function beginReplay(PublicProjectionOutboxReplayRequest $request): void
    {
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($request->cursorIdentity->sourceModule);
        $statement = $this->connection->prepare("INSERT INTO {$schema}.public_projection_outbox_replays (consumer_id,source_module,aggregate_type,aggregate_id,from_version,from_index,to_version,to_index) VALUES (:consumer,:module,:type,:id,:fv,:fi,:tv,:ti)");
        $statement->execute($this->identity($request->cursorIdentity) + ['fv' => $request->fromExclusive?->aggregateVersion, 'fi' => $request->fromExclusive?->eventIndex->value, 'tv' => $request->toInclusive->aggregateVersion, 'ti' => $request->toInclusive->eventIndex->value]);
    }

    /** @return array<string, string> */
    private function identity(PublicProjectionOutboxCursorIdentity $identity): array
    {
        return ['consumer' => $identity->consumerId->value, 'module' => $identity->sourceModule->value, 'type' => $identity->aggregateType->value, 'id' => $identity->aggregateId->value];
    }

    private function order(mixed $version, mixed $index): ?PublicProjectionDeliveryOrder
    {
        return $version === null ? null : new PublicProjectionDeliveryOrder((int) $version, PublicProjectionDeliveryEventIndex::fromInt((int) $index));
    }
}
