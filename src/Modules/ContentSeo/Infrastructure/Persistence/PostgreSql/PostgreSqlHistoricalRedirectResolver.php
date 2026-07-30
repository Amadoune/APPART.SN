<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectResolution;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalRedirectDecisionMapper;
use PDO;

final readonly class PostgreSqlHistoricalRedirectResolver implements HistoricalRedirectResolver
{
    public function __construct(private PDO $connection, private HistoricalRedirectDecisionMapper $mapper) {}

    public function resolve(HistoricalCanonical $canonical): HistoricalRedirectResolution
    {
        $statement = $this->connection->prepare(
            'SELECT decision_id::text,historical_canonical,destination_canonical,destination_qualification,revision,decision_checksum,integrity_status
             FROM content_seo.historical_redirect_decisions
             WHERE historical_canonical=:historical_canonical
             ORDER BY decision_id
             LIMIT 2',
        );
        $statement->execute(['historical_canonical' => $canonical->canonical->value]);

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $this->mapper->toResolution($canonical, $rows);
    }
}
