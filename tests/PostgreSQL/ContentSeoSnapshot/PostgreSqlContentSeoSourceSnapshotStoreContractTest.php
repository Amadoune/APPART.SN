<?php

namespace Tests\PostgreSQL\ContentSeoSnapshot;

use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotWriter;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoSourceSnapshotMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotWriter;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\ContentSeoSnapshot\ContentSeoSourceSnapshotStoreContract;

final class PostgreSqlContentSeoSourceSnapshotStoreContractTest extends ContentSeoSourceSnapshotStoreContract
{
    private PDO $connection;

    private ContentSeoSourceSnapshotMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->mapper = new ContentSeoSourceSnapshotMapper;
    }

    protected function reader(): ContentSeoSourceSnapshotReader
    {
        return new PostgreSqlContentSeoSourceSnapshotReader($this->connection, $this->mapper);
    }

    protected function writer(): ContentSeoSourceSnapshotWriter
    {
        return new PostgreSqlContentSeoSourceSnapshotWriter($this->connection, $this->mapper);
    }
}
