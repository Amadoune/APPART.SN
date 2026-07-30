<?php

namespace Tests\PostgreSQL\ModerationQueueOwnerReadSource;

use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadFilterV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadStateV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadStatusV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueCursorCodecV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueReadMapperV1;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationQueueOwnerReadSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationQueueOwnerReadSourceV1 $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->source = new PostgreSqlModerationQueueOwnerReadSourceV1(
            $this->connection,
            new ModerationQueueReadMapperV1,
            new ModerationQueueCursorCodecV1('postgresql-test-integrity-key'),
        );
    }

    #[Test]
    public function state_and_category_are_keyset_paginated_in_canonical_order_without_mutation(): void
    {
        $this->insert(1, 50, 'fraud', '2026-07-30T10:00:02+00:00');
        $this->insert(2, 90, 'fraud', '2026-07-30T10:00:03+00:00');
        $this->insert(3, 90, 'fraud', '2026-07-30T10:00:01+00:00');
        $this->insert(4, 100, 'spam', '2026-07-30T10:00:00+00:00');
        $filter = new ModerationQueueReadFilterV1(ModerationQueueReadStateV1::Available, 'fraud');

        $first = $this->source->read($filter, null, 2);
        self::assertSame(ModerationQueueReadStatusV1::PageAvailable, $first->status);
        self::assertSame([$this->id(3), $this->id(2)], array_map(
            static fn ($item): string => $item->queueItemId,
            $first->page?->items ?? [],
        ));
        self::assertNotNull($first->page?->nextCursor);

        $second = $this->source->read($filter, $first->page?->nextCursor, 2);
        self::assertSame([$this->id(1)], array_map(
            static fn ($item): string => $item->queueItemId,
            $second->page?->items ?? [],
        ));
        self::assertNull($second->page?->nextCursor);
        self::assertSame(4, (int) $this->connection->query('SELECT count(*) FROM moderation_reports.queue_items')->fetchColumn());
    }

    #[Test]
    public function invalid_cursor_empty_page_and_bounds_are_fail_closed(): void
    {
        $filter = new ModerationQueueReadFilterV1(ModerationQueueReadStateV1::Available);

        self::assertSame(ModerationQueueReadStatusV1::Empty, $this->source->read($filter, null, 25)->status);
        self::assertSame(ModerationQueueReadStatusV1::InvalidCursor, $this->source->read($filter, 'invalid', 25)->status);
        self::assertSame(ModerationQueueReadStatusV1::Corrupted, $this->source->read($filter, null, 0)->status);
        self::assertSame(ModerationQueueReadStatusV1::Corrupted, $this->source->read($filter, null, 101)->status);
    }

    #[Test]
    public function category_index_is_additive_reversible_and_available_to_the_planner(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame(
            1,
            (int) $this->connection->query(
                "SELECT count(*) FROM pg_indexes
                 WHERE schemaname='moderation_reports'
                   AND indexname='moderation_reports_queue_category_read_idx'",
            )->fetchColumn(),
        );
        $this->insert(10, 75, 'fraud', '2026-07-30T10:00:00+00:00');
        $this->connection->exec('SET enable_seqscan = off');
        $plan = $this->connection->query(
            "EXPLAIN (COSTS OFF)
             SELECT queue_item_id FROM moderation_reports.queue_items
             WHERE state='Available' AND category='fraud'
             ORDER BY priority DESC,updated_at ASC,queue_item_id ASC LIMIT 26",
        )->fetchAll(PDO::FETCH_COLUMN);
        self::assertStringContainsString(
            'moderation_reports_queue_category_read_idx',
            implode("\n", array_map('strval', $plan)),
        );

        $this->connection->exec((string) file_get_contents($root.'070_moderation_queue_owner_read_source.down.sql'));
        self::assertSame(
            0,
            (int) $this->connection->query(
                "SELECT count(*) FROM pg_indexes
                 WHERE schemaname='moderation_reports'
                   AND indexname='moderation_reports_queue_category_read_idx'",
            )->fetchColumn(),
        );
        $this->connection->exec((string) file_get_contents($root.'070_moderation_queue_owner_read_source.sql'));
    }

    private function insert(int $suffix, int $priority, string $category, string $updatedAt): void
    {
        $statement = $this->connection->prepare(
            "INSERT INTO moderation_reports.queue_items
             (queue_item_id,case_id,priority,category,state,lease_id,claim_owner_id,lease_expires_at,source_version,updated_at)
             VALUES(CAST(:queue_item_id AS uuid),CAST(:case_id AS uuid),:priority,:category,'Available',NULL,NULL,NULL,1,CAST(:updated_at AS timestamptz))",
        );
        $statement->execute([
            'queue_item_id' => $this->id($suffix),
            'case_id' => $this->caseId($suffix),
            'priority' => $priority,
            'category' => $category,
            'updated_at' => $updatedAt,
        ]);
    }

    private function id(int $suffix): string
    {
        return sprintf('53b20000-0000-4000-8000-%012d', $suffix);
    }

    private function caseId(int $suffix): string
    {
        return sprintf('53b30000-0000-4000-8000-%012d', $suffix);
    }
}
