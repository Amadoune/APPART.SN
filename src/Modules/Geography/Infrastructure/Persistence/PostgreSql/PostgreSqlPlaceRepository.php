<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Exception\ConcurrentPlaceModification;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceCode;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceId;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Infrastructure\Persistence\PersistentPlaceIntegrity;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceAliasSnapshot;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceSnapshot;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceTransaction;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlPlaceRepository implements PlaceRegistry
{
    private PlaceTransaction $transaction;

    public function __construct(private PDO $connection, private PlaceMapper $mapper, ?PlaceTransaction $transaction = null)
    {
        $this->transaction = $transaction ?? new PostgreSqlPlaceTransaction($connection);
    }

    public function find(PlaceId $id): ?Place
    {
        try {
            $statement = $this->connection->prepare('SELECT p.id::text,p.official_name,p.code,p.type,p.country_code,p.parent_place_id::text,parent.type AS parent_type,p.latitude,p.longitude,p.enabled,p.merged_into_place_id::text,p.aggregate_version FROM geography.places p LEFT JOIN geography.places parent ON parent.id=p.parent_place_id WHERE p.id=:id');
            $statement->execute(['id' => $id->value]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }

            return $this->mapper->toAggregate($this->snapshot($row, $this->aliases($id->value)));
        } catch (PersistentPlaceIntegrity $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentPlaceIntegrity::invalid('read');
        }
    }

    public function add(Place $place): void
    {
        $snapshot = $this->mapper->toSnapshot($place);
        try {
            $this->transaction->run(function () use ($snapshot): void {
                $this->insertRoot($snapshot);
                $this->replaceAliases($snapshot);
            });
        } catch (PDOException $error) {
            if ($error->getCode() === '23505') {
                if (str_contains($error->getMessage(), 'places_country_code_code_uq')) {
                    throw DuplicatePlaceCode::forCountry($place->code(), $place->countryCode());
                }
                throw DuplicatePlaceId::forId($place->id());
            }
            throw PersistentPlaceIntegrity::invalid('write');
        } catch (DuplicatePlaceId|DuplicatePlaceCode $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentPlaceIntegrity::invalid('write');
        }
    }

    public function save(Place $place, int $expectedVersion): void
    {
        $snapshot = $this->mapper->toSnapshot($place);
        if ($snapshot->version <= $expectedVersion) {
            throw new ConcurrentPlaceModification;
        }
        try {
            $this->transaction->run(function () use ($snapshot, $expectedVersion): void {
                $statement = $this->connection->prepare('UPDATE geography.places SET official_name=:official_name,code=:code,type=:type,country_code=:country_code,parent_place_id=CAST(:parent_id AS uuid),latitude=:latitude,longitude=:longitude,enabled=:enabled,merged_into_place_id=CAST(:merged_into AS uuid),aggregate_version=:version WHERE id=CAST(:id AS uuid) AND aggregate_version=:expected_version');
                $statement->execute($this->parameters($snapshot) + ['expected_version' => $expectedVersion]);
                if ($statement->rowCount() !== 1) {
                    throw new ConcurrentPlaceModification;
                }
                $this->replaceAliases($snapshot);
            });
        } catch (ConcurrentPlaceModification $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentPlaceIntegrity::invalid('write');
        }
    }

    private function insertRoot(PlaceSnapshot $snapshot): void
    {
        $statement = $this->connection->prepare('INSERT INTO geography.places(id,official_name,code,type,country_code,parent_place_id,latitude,longitude,enabled,merged_into_place_id,aggregate_version) VALUES(CAST(:id AS uuid),:official_name,:code,:type,:country_code,CAST(:parent_id AS uuid),:latitude,:longitude,:enabled,CAST(:merged_into AS uuid),:version)');
        $statement->execute($this->parameters($snapshot));
    }

    private function replaceAliases(PlaceSnapshot $snapshot): void
    {
        $delete = $this->connection->prepare('DELETE FROM geography.place_aliases WHERE place_id=CAST(:id AS uuid)');
        $delete->execute(['id' => $snapshot->id]);
        $insert = $this->connection->prepare('INSERT INTO geography.place_aliases(place_id,name,normalization_key,recorded_at) VALUES(CAST(:id AS uuid),:name,:key,CAST(:recorded_at AS timestamptz))');
        foreach ($snapshot->aliases as $alias) {
            $insert->execute(['id' => $snapshot->id, 'name' => $alias->name, 'key' => mb_strtolower($alias->name), 'recorded_at' => $alias->recordedAt]);
        }
    }

    /** @return list<PlaceAliasSnapshot> */
    private function aliases(string $id): array
    {
        $statement = $this->connection->prepare('SELECT name,to_char(recorded_at AT TIME ZONE \'UTC\',\'YYYY-MM-DD"T"HH24:MI:SS.US"+00:00"\') AS recorded_at FROM geography.place_aliases WHERE place_id=CAST(:id AS uuid) ORDER BY normalization_key');
        $statement->execute(['id' => $id]);

        return array_map(static fn (array $row): PlaceAliasSnapshot => new PlaceAliasSnapshot((string) $row['name'], (string) $row['recorded_at']), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<PlaceAliasSnapshot>  $aliases
     */
    private function snapshot(array $row, array $aliases): PlaceSnapshot
    {
        $enabled = $row['enabled'] === true || $row['enabled'] === 1 || $row['enabled'] === '1' || $row['enabled'] === 't';

        return new PlaceSnapshot((string) $row['id'], (string) $row['official_name'], (string) $row['code'], (string) $row['type'], (string) $row['country_code'], $row['parent_place_id'] === null ? null : (string) $row['parent_place_id'], $row['parent_type'] === null ? null : (string) $row['parent_type'], $row['latitude'] === null ? null : (float) $row['latitude'], $row['longitude'] === null ? null : (float) $row['longitude'], $aliases, $enabled, $row['merged_into_place_id'] === null ? null : (string) $row['merged_into_place_id'], (int) $row['aggregate_version']);
    }

    /** @return array<string, bool|float|int|string|null> */
    private function parameters(PlaceSnapshot $snapshot): array
    {
        return ['id' => $snapshot->id, 'official_name' => $snapshot->officialName, 'code' => $snapshot->code, 'type' => $snapshot->type, 'country_code' => $snapshot->countryCode, 'parent_id' => $snapshot->parentId, 'latitude' => $snapshot->latitude, 'longitude' => $snapshot->longitude, 'enabled' => $snapshot->enabled ? 'true' : 'false', 'merged_into' => $snapshot->mergedInto, 'version' => $snapshot->version];
    }
}
