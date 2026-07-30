<?php

namespace Tests\PostgreSQL\MediaIngestionEventDelivery;

use App\Application\MediaIngestionEventDelivery\MediaIngestionDeliveryConsumer;
use App\Application\MediaIngestionEventRouting\DeterministicMediaIngestionEventRouter;
use App\Application\MediaIngestionEventRouting\MediaIngestionRoutingDestination;
use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;
use App\Application\MediaIngestionEventTransport\MediaIngestionEventTransportSerializer;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventType;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventV1;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaIngestionEventDeliveryTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function transport_routing_and_delivery_do_not_mutate_persistence(): void
    {
        $serializer = new MediaIngestionEventTransportSerializer;
        $event = new MediaIngestionEventV1(
            MediaIngestionEventType::AssetReady,
            '5b000000-0000-4000-8000-000000000001',
            1,
            'media-policy-v1',
            new DateTimeImmutable('2026-07-28T10:00:00+00:00'),
            new DateTimeImmutable('2026-07-28T10:00:01+00:00'),
            '5b000000-0000-4000-8000-000000000002',
            '5b000000-0000-4000-8000-000000000003',
        );
        $message = new MediaIngestionDeliveryMessageV1($event);
        $routing = (new DeterministicMediaIngestionEventRouter($serializer))->route($message);
        self::assertContains(MediaIngestionRoutingDestination::MediaAttachment, $routing->destinations);
        $fact = (new MediaIngestionDeliveryConsumer($serializer))->consume($message, MediaIngestionRoutingDestination::MediaAttachment);
        self::assertSame($message->messageId, $fact->messageId);
        self::assertSame(0, (int) $this->connection->query('SELECT count(*) FROM media_ingestion.assets')->fetchColumn());
        self::assertSame(0, (int) $this->connection->query('SELECT count(*) FROM media.media_attachment_intents')->fetchColumn());
        self::assertFalse($this->connection->inTransaction());
    }
}
