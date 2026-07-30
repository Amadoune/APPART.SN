<?php

namespace Tests\PostgreSQL\HistoricalRedirect;

use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalRedirectDecisionMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalRedirectResolver;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\HistoricalRedirect\HistoricalRedirectResolverContract;

final class PostgreSqlHistoricalRedirectResolverContractTest extends HistoricalRedirectResolverContract
{
    private PDO $connection;

    private HistoricalRedirectDecisionMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->mapper = new HistoricalRedirectDecisionMapper;
    }

    protected function resolver(): HistoricalRedirectResolver
    {
        return new PostgreSqlHistoricalRedirectResolver($this->connection, $this->mapper);
    }

    protected function persist(HistoricalCanonical $source, ?CanonicalUrl $destination, ?string $qualification, int $revision = 1): void
    {
        $row = [
            'decision_id' => sprintf('13000000-0000-4000-8000-%012d', $revision),
            'historical_canonical' => $source->canonical->value,
            'destination_canonical' => $destination?->value,
            'destination_qualification' => $qualification,
            'revision' => $revision,
        ];
        $statement = $this->connection->prepare('INSERT INTO content_seo.historical_redirect_decisions(decision_id,historical_canonical,destination_canonical,destination_qualification,revision,decision_checksum,integrity_status) VALUES(CAST(:decision_id AS uuid),:historical_canonical,:destination_canonical,:destination_qualification,:revision,:decision_checksum,\'intact\')');
        $statement->execute($row + ['decision_checksum' => $this->mapper->checksum($row)]);
    }
}
