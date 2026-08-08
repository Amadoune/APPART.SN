<?php

namespace Tests\PostgreSQL\ContentSeoOutbox;

use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryPayload;
use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryStatus;
use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryV1;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryPayload;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryStatus;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryV1;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventType;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventType;
use Appart\Modules\ContentSeo\Application\Outbox\ContentSeoOutboxPolicy;
use Appart\Modules\ContentSeo\Application\Outbox\ContentSeoOutboxStatus;
use Appart\Modules\ContentSeo\Infrastructure\Outbox\PostgreSqlContentSeoOutboxRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlContentSeoOutboxRepositoryTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlContentSeoOutboxRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->connection->exec((string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/078_editorial_content_operational_seo_owner_source.sql'));
        $this->connection->exec((string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/ContentSeo/Infrastructure/Outbox/Migrations/081_content_seo_outbox.sql'));
        $this->connection->exec('TRUNCATE content_seo.content_seo_outbox');
        $this->repository = new PostgreSqlContentSeoOutboxRepository($this->connection, new ContentSeoOutboxPolicy);
    }

    public function test_both_streams_are_idempotent_and_pending_is_ordered(): void
    {
        $editorial = $this->editorial();
        $operational = $this->operational();
        self::assertSame(ContentSeoOutboxStatus::Applied, $this->repository->append($editorial)->status);
        self::assertSame(ContentSeoOutboxStatus::AlreadyApplied, $this->repository->append($editorial)->status);
        self::assertSame(ContentSeoOutboxStatus::Applied, $this->repository->append($operational)->status);
        $pending = $this->repository->pending(10);
        self::assertCount(2, $pending);
        self::assertContains($editorial->payload->canonical(), array_map(static fn ($entry): array => $entry->delivery->payload->canonical(), $pending));
        self::assertContains($operational->payload->canonical(), array_map(static fn ($entry): array => $entry->delivery->payload->canonical(), $pending));
    }

    public function test_divergence_retry_bound_and_external_rollback_are_preserved(): void
    {
        $delivery = $this->editorial();
        $entry = $this->repository->append($delivery);
        $statement = $this->connection->prepare('UPDATE content_seo.content_seo_outbox SET message_checksum=:checksum WHERE message_id=:id');
        $statement->execute(['checksum' => str_repeat('0', 64), 'id' => $entry->messageId]);
        self::assertSame(ContentSeoOutboxStatus::DivergentMessage, $this->repository->append($delivery)->status);
        $this->connection->exec('UPDATE content_seo.content_seo_outbox SET retry_count=10');
        self::assertSame([], $this->repository->pending(10));
        $second = $this->operational();
        $this->connection->beginTransaction();
        self::assertSame(ContentSeoOutboxStatus::Applied, $this->repository->append($second)->status);
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        $query = $this->connection->prepare('SELECT count(*) FROM content_seo.content_seo_outbox WHERE message_id=:id');
        $query->execute(['id' => (new ContentSeoOutboxPolicy)->messageId($second)]);
        self::assertSame(0, (int) $query->fetchColumn());
    }

    private function editorial(): EditorialContentDeliveryV1
    {
        return new EditorialContentDeliveryV1(new EditorialContentDeliveryPayload(EditorialContentEventType::Observed, EditorialContentDeliveryStatus::Published, '2026-08-02T12:00:00.123456Z'));
    }

    private function operational(): OperationalSeoDeliveryV1
    {
        return new OperationalSeoDeliveryV1(new OperationalSeoDeliveryPayload(OperationalSeoEventType::Observed, OperationalSeoDeliveryStatus::Indexable, '2026-08-02T12:00:00.123456Z'));
    }
}
