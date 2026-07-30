<?php

namespace Tests\PostgreSQL\Media;

use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionRepository;
use PDO;
use PHPUnit\Framework\Assert;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\Media\FakeMediaCollectionRegistryHarness;

final class PostgreSqlMediaCollectionRegistryHarness extends FakeMediaCollectionRegistryHarness
{
    private ?FailBeforeCommitMediaCollectionTransaction $transaction = null;

    public function freshRegistry(): MediaCollectionRegistry
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $this->transaction = new FailBeforeCommitMediaCollectionTransaction($connection);

        return new PostgreSqlMediaCollectionRepository($connection, new MediaCollectionMapper, $this->transaction);
    }

    public function failNextWrite(MediaCollectionRegistry $registry): void
    {
        Assert::assertInstanceOf(PostgreSqlMediaCollectionRepository::class, $registry);
        Assert::assertNotNull($this->transaction);
        $this->transaction->failNext();
    }

    public function independentConnection(): PDO
    {
        return PostgreSqlTestEnvironment::connection();
    }
}
