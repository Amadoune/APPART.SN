<?php

namespace Tests\PostgreSQL\HistoricalCanonicalQualification;

use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalCanonicalQualificationMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalCanonicalQualifier;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\HistoricalCanonicalQualification\HistoricalCanonicalQualifierContract;

final class PostgreSqlHistoricalCanonicalQualifierContractTest extends HistoricalCanonicalQualifierContract
{
    private PDO $connection;

    private HistoricalCanonicalQualificationMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->mapper = new HistoricalCanonicalQualificationMapper;
    }

    protected function qualifier(): HistoricalCanonicalQualifier
    {
        return new PostgreSqlHistoricalCanonicalQualifier($this->connection, $this->mapper);
    }

    protected function persist(CanonicalUrl $canonical, string $qualification, int $revision = 1): void
    {
        $row = [
            'decision_id' => sprintf('14000000-0000-4000-8000-%012d', $revision),
            'canonical' => $canonical->value,
            'qualification' => $qualification,
            'revision' => $revision,
        ];
        $statement = $this->connection->prepare('INSERT INTO content_seo.historical_canonical_qualifications(decision_id,canonical,qualification,revision,decision_checksum,integrity_status) VALUES(CAST(:decision_id AS uuid),:canonical,:qualification,:revision,:decision_checksum,\'intact\')');
        $statement->execute($row + ['decision_checksum' => $this->mapper->checksum($row)]);
    }
}
