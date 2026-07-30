<?php

namespace Tests\PostgreSQL\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use PDO;
use PHPUnit\Framework\Assert;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\ListingLifecycle\FakeListingRegistryHarness;

final class PostgreSqlListingRegistryHarness extends FakeListingRegistryHarness
{
    private ?FailBeforeCommitListingTransaction $transaction = null;

    public function freshRegistry(): ListingRegistry
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $this->transaction = new FailBeforeCommitListingTransaction($connection);

        return new PostgreSqlListingRepository($connection, new ListingMapper, $this->transaction);
    }

    public function failNextWrite(ListingRegistry $registry): void
    {
        Assert::assertInstanceOf(PostgreSqlListingRepository::class, $registry);
        Assert::assertNotNull($this->transaction);
        $this->transaction->failNext();
    }

    public function independentConnection(): PDO
    {
        return PostgreSqlTestEnvironment::connection();
    }
}
