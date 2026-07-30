<?php

namespace Tests\PostgreSQL\Media;

use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionRepository;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\Media\FakeMediaCollectionRegistryHarness;

final class PostgreSqlMediaCollectionRepositoryIntegrationTest extends TestCase
{
    public function test_complete_snapshot_and_permanent_reservations_are_persisted(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $repository = new PostgreSqlMediaCollectionRepository($connection, new MediaCollectionMapper);
        $h = new FakeMediaCollectionRegistryHarness;
        $collection = $h->emptyCollection();
        $repository->add($collection);
        $h->addMedia($collection, $h->firstMediaId(), 1);
        $repository->saveWithMediaReservation($collection, $h->firstMediaId(), 0);
        $h->addMedia($collection, $h->secondMediaId(), 2);
        $repository->saveWithMediaReservation($collection, $h->secondMediaId(), 1);
        $h->archiveSecond($collection);
        $repository->save($collection, 2);
        $stored = $repository->find($collection->id());
        self::assertNotNull($stored);
        self::assertEquals($collection->items(), $stored->items());
        self::assertSame(3, $stored->version());
        self::assertSame([], $stored->releaseEvents());
        self::assertSame(2, (int) $connection->query('SELECT count(*) FROM media.media_id_reservations')->fetchColumn());
    }
}
