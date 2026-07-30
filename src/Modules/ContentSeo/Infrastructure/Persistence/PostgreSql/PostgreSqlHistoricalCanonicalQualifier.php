<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualification;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalCanonicalQualificationMapper;
use PDO;

final readonly class PostgreSqlHistoricalCanonicalQualifier implements HistoricalCanonicalQualifier
{
    public function __construct(private PDO $connection, private HistoricalCanonicalQualificationMapper $mapper) {}

    public function qualify(CanonicalUrl $canonical): HistoricalCanonicalQualification
    {
        $statement = $this->connection->prepare(
            'SELECT decision_id::text,canonical,qualification,revision,decision_checksum,integrity_status
             FROM content_seo.historical_canonical_qualifications
             WHERE canonical=:canonical
             ORDER BY decision_id
             LIMIT 2',
        );
        $statement->execute(['canonical' => $canonical->value]);

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $this->mapper->toQualification($canonical, $rows);
    }
}
