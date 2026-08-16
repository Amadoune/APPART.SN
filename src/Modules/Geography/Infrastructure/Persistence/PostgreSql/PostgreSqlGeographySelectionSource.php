<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionSource;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionSourceItem;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionSourceResult;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use PDO;
use Throwable;

final readonly class PostgreSqlGeographySelectionSource implements GeographySelectionSource
{
    public function __construct(private PDO $connection) {}

    public function select(PlaceType $type, ?PlaceId $parentId, ?string $afterNormalizationKey, ?PlaceId $afterPlaceId, int $limit): GeographySelectionSourceResult
    {
        try {
            if ($parentId !== null) {
                $parentType = $this->parentType($parentId);
                if ($parentType === null) {
                    return GeographySelectionSourceResult::missing();
                }
                if (! $type->acceptsParent($parentType)) {
                    return GeographySelectionSourceResult::corrupted();
                }
            }
            $sql = 'SELECT id::text,official_name,type,parent_place_id::text,lower(official_name) AS normalization_key FROM geography.places WHERE type=:type AND enabled=true AND merged_into_place_id IS NULL AND '.($parentId === null ? 'parent_place_id IS NULL' : 'parent_place_id=CAST(:parent AS uuid)');
            $parameters = ['type' => $type->value];
            if ($parentId !== null) {
                $parameters['parent'] = $parentId->value;
            }
            if ($afterNormalizationKey !== null && $afterPlaceId !== null) {
                $sql .= ' AND (lower(official_name),id) > (:after_key,CAST(:after_id AS uuid))';
                $parameters['after_key'] = $afterNormalizationKey;
                $parameters['after_id'] = $afterPlaceId->value;
            }
            $sql .= ' ORDER BY lower(official_name),id LIMIT '.max(1, min(101, $limit));
            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($rows === []) {
                return GeographySelectionSourceResult::empty();
            }
            $items = array_map(function (array $row) use ($type, $parentId): GeographySelectionSourceItem {
                $id = PlaceId::fromString((string) $row['id']);
                $name = PlaceName::fromString((string) $row['official_name']);
                if ($row['type'] !== $type->value || ($row['parent_place_id'] === null ? null : (string) $row['parent_place_id']) !== $parentId?->value || $row['normalization_key'] !== $name->normalizationKey()) {
                    throw new \RuntimeException('Corrupted Geography selection row.');
                }

                return new GeographySelectionSourceItem($id->value, $name->value, $type->value, $parentId?->value, $name->normalizationKey());
            }, $rows);

            return GeographySelectionSourceResult::available($items);
        } catch (Throwable) {
            return $this->available() ? GeographySelectionSourceResult::corrupted() : GeographySelectionSourceResult::unavailable();
        }
    }

    private function parentType(PlaceId $id): ?PlaceType
    {
        $statement = $this->connection->prepare('SELECT type FROM geography.places WHERE id=CAST(:id AS uuid)');
        $statement->execute(['id' => $id->value]);
        $type = $statement->fetchColumn();

        return is_string($type) ? PlaceType::tryFrom($type) : null;
    }

    private function available(): bool
    {
        try {
            $statement = $this->connection->query("SELECT to_regclass('geography.places')::text");

            return $statement !== false && is_string($statement->fetchColumn());
        } catch (Throwable) {
            return false;
        }
    }
}
