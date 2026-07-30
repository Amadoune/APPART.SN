<?php

namespace Tests\PostgreSQL\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\Contract\AdministrativeActionRegistry;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionRepository;
use PDO;
use PHPUnit\Framework\Assert;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\AdministrationAudit\FakeAdministrativeActionRegistryHarness;

final class PostgreSqlAdministrativeActionRegistryHarness extends FakeAdministrativeActionRegistryHarness
{
    private ?FailBeforeCommitAdministrativeActionTransaction $transaction = null;

    public function freshRegistry(): AdministrativeActionRegistry
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $this->transaction = new FailBeforeCommitAdministrativeActionTransaction($connection);

        return new PostgreSqlAdministrativeActionRepository($connection, new AdministrativeActionMapper, $this->transaction);
    }

    public function failNextWrite(AdministrativeActionRegistry $registry): void
    {
        Assert::assertInstanceOf(PostgreSqlAdministrativeActionRepository::class, $registry);
        Assert::assertNotNull($this->transaction);
        $this->transaction->failNext();
    }

    public function independentConnection(): PDO
    {
        return PostgreSqlTestEnvironment::connection();
    }
}
