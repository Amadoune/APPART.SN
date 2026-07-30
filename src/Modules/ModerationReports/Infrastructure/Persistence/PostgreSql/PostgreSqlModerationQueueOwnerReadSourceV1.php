<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\Contract\ModerationQueueOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadFilterV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadPageV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadResultV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueCursorCodecV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueCursorPositionV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueReadMapperV1;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlModerationQueueOwnerReadSourceV1 implements ModerationQueueOwnerReadSourceV1
{
    public function __construct(
        private PDO $connection,
        private ModerationQueueReadMapperV1 $mapper,
        private ModerationQueueCursorCodecV1 $cursorCodec,
    ) {}

    public function read(
        ModerationQueueReadFilterV1 $filter,
        ?string $cursor,
        int $limit,
    ): ModerationQueueReadResultV1 {
        if ($limit < 1 || $limit > 100 || ($filter->category !== null && ($filter->category === '' || strlen($filter->category) > 64))) {
            return ModerationQueueReadResultV1::corrupted();
        }
        $position = $cursor === null ? null : $this->cursorCodec->decode($cursor, $filter->checksum());
        if ($cursor !== null && $position === null) {
            return ModerationQueueReadResultV1::invalidCursor();
        }

        try {
            $sql = 'SELECT queue_item_id,case_id,priority,category,state,lease_expires_at,source_version,updated_at
                    FROM moderation_reports.queue_items
                    WHERE state=:state';
            $parameters = ['state' => $filter->state->value];
            if ($filter->category !== null) {
                $sql .= ' AND category=:category';
                $parameters['category'] = $filter->category;
            }
            if ($position !== null) {
                $sql .= ' AND (priority < :priority OR
                              (priority = :priority AND updated_at > CAST(:updated_at AS timestamptz)) OR
                              (priority = :priority AND updated_at = CAST(:updated_at AS timestamptz)
                               AND queue_item_id > CAST(:queue_item_id AS uuid)))';
                $parameters += [
                    'priority' => $position->priority,
                    'updated_at' => $position->updatedAt->format('Y-m-d\TH:i:s.uP'),
                    'queue_item_id' => $position->queueItemId,
                ];
            }
            $sql .= ' ORDER BY priority DESC,updated_at ASC,queue_item_id ASC LIMIT '.($limit + 1);
            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($rows === []) {
                return ModerationQueueReadResultV1::empty();
            }
            $hasNext = count($rows) > $limit;
            $rows = array_slice($rows, 0, $limit);
            $items = array_map($this->mapper->item(...), $rows);
            $nextCursor = null;
            if ($hasNext) {
                $last = $items[array_key_last($items)];
                $nextCursor = $this->cursorCodec->encode(
                    new ModerationQueueCursorPositionV1($last->priority, $last->updatedAt, $last->queueItemId),
                    $filter->checksum(),
                );
            }

            return ModerationQueueReadResultV1::page(new ModerationQueueReadPageV1($items, $nextCursor));
        } catch (PDOException) {
            return ModerationQueueReadResultV1::dependencyUnavailable();
        } catch (Throwable) {
            return ModerationQueueReadResultV1::corrupted();
        }
    }
}
