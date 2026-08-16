<?php

namespace App\Infrastructure\PublicGeographySource\PostgreSql;

use App\Application\PublicGeographyRefresh\AffectedPublicGeographyTerminalPage;
use App\Application\PublicGeographyRefresh\AffectedPublicGeographyTerminalStatus;
use App\Application\PublicGeographyRefresh\Contract\AffectedPublicGeographyTerminalReaderV1;
use App\Application\PublicGeographySource\PublicGeographyDecisionV2;
use PDO;
use Throwable;

final readonly class PostgreSqlAffectedPublicGeographyTerminalReader implements AffectedPublicGeographyTerminalReaderV1
{
    public function __construct(private PDO $connection) {}

    public function read(string $mutatedPlaceId, ?string $cursor = null, int $limit = 100): AffectedPublicGeographyTerminalPage
    {
        if (trim($mutatedPlaceId) === '' || $limit < 1 || $limit > 500) {
            return new AffectedPublicGeographyTerminalPage(AffectedPublicGeographyTerminalStatus::Corrupted);
        }
        $after = '';
        if ($cursor !== null) {
            $decoded = base64_decode(strtr($cursor, '-_', '+/'), true);
            if ($decoded === false || trim($decoded) === '') {
                return new AffectedPublicGeographyTerminalPage(AffectedPublicGeographyTerminalStatus::Corrupted);
            }
            $after = $decoded;
        }
        try {
            $statement = $this->connection->prepare("SELECT place_id FROM public_geography.decisions WHERE place_id > :after AND payload->>'schemaVersion'=:schema AND EXISTS (SELECT 1 FROM jsonb_array_elements(payload->'revisionVector') item WHERE item->>'placeId'=:mutated) ORDER BY place_id ASC LIMIT :limit");
            $statement->bindValue('after', $after);
            $statement->bindValue('schema', PublicGeographyDecisionV2::SCHEMA_VERSION);
            $statement->bindValue('mutated', $mutatedPlaceId);
            $statement->bindValue('limit', $limit + 1, PDO::PARAM_INT);
            $statement->execute();
            $ids = array_map(static fn (array $row): string => (string) $row['place_id'], $statement->fetchAll(PDO::FETCH_ASSOC));
            if ($ids === []) {
                return new AffectedPublicGeographyTerminalPage(AffectedPublicGeographyTerminalStatus::Empty);
            }
            $hasMore = count($ids) > $limit;
            if ($hasMore) {
                array_pop($ids);
            }
            $next = $hasMore ? rtrim(strtr(base64_encode($ids[array_key_last($ids)]), '+/', '-_'), '=') : null;

            return new AffectedPublicGeographyTerminalPage(AffectedPublicGeographyTerminalStatus::Available, $ids, $next);
        } catch (Throwable) {
            return new AffectedPublicGeographyTerminalPage(AffectedPublicGeographyTerminalStatus::DependencyUnavailable);
        }
    }
}
