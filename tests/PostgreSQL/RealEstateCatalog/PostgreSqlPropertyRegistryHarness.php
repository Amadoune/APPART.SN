<?php

namespace Tests\PostgreSQL\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyMapper;
use PDO;
use PHPUnit\Framework\Assert;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\RealEstateCatalog\FakePropertyRegistryHarness;

final class PostgreSqlPropertyRegistryHarness extends FakePropertyRegistryHarness
{
    private ?FailBeforeCommitPropertyTransaction $transaction = null;

    public function freshRegistry(): PropertyRegistry
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $this->transaction = new FailBeforeCommitPropertyTransaction($connection);

        return new PostgreSqlPropertyRepository($connection, new PropertyMapper, $this->transaction);
    }

    public function failNextWrite(PropertyRegistry $registry): void
    {
        Assert::assertInstanceOf(PostgreSqlPropertyRepository::class, $registry);
        Assert::assertNotNull($this->transaction);
        $this->transaction->failNext();
    }

    public function independentConnection(): PDO
    {
        return PostgreSqlTestEnvironment::connection();
    }
}
