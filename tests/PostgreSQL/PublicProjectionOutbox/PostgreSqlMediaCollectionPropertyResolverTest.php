<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\PublicProjectionSourceLookup\MediaCollectionPropertyStatus;
use App\Infrastructure\PublicProjectionSourceLookup\RegistryMediaCollectionPropertyResolver;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaCollectionPropertyResolverTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_registry_adapter_reads_the_official_media_property_ownership(): void
    {
        $media = '93000000-0000-4000-8000-000000000001';
        $property = '92000000-0000-4000-8000-000000000001';
        $statement = $this->connection->prepare("INSERT INTO media.media_collections(id,property_id,last_changed_at,last_changed_at_offset,version) VALUES (:media,:property,'2026-07-19T10:00:00+00:00',0,0)");
        $statement->execute(['media' => $media, 'property' => $property]);
        $resolver = new RegistryMediaCollectionPropertyResolver(new PostgreSqlMediaCollectionRepository($this->connection, new MediaCollectionMapper));

        $found = $resolver->resolve($media);
        self::assertSame(MediaCollectionPropertyStatus::Found, $found->status);
        self::assertSame($property, $found->propertyId);
        self::assertSame(MediaCollectionPropertyStatus::Missing, $resolver->resolve('93000000-0000-4000-8000-000000000002')->status);
    }
}
