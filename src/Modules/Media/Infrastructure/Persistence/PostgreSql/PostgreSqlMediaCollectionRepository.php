<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Domain\Exception\ConcurrentMediaCollectionModification;
use Appart\Modules\Media\Domain\Exception\MediaCollectionIdConflict;
use Appart\Modules\Media\Domain\Exception\MediaIdConflict;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionSnapshot;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionTransaction;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemSnapshot;
use Appart\Modules\Media\Infrastructure\Persistence\PersistentMediaCollectionIntegrity;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlMediaCollectionRepository implements MediaCollectionRegistry
{
    private MediaCollectionTransaction $transaction;

    public function __construct(private PDO $connection, private MediaCollectionMapper $mapper, ?MediaCollectionTransaction $transaction = null)
    {
        $this->transaction = $transaction ?? new PostgreSqlMediaCollectionTransaction($connection);
    }

    public function find(MediaCollectionId $id): ?MediaCollection
    {
        $s = $this->connection->prepare('SELECT id,property_id,last_changed_at,last_changed_at_offset,version FROM media.media_collections WHERE id=:id');
        $s->execute(['id' => $id->value]);
        $row = $s->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->toAggregate($this->snapshot($row));
    }

    public function add(MediaCollection $collection): void
    {
        $x = $this->mapper->toSnapshot($collection);
        try {
            $this->transaction->run(function () use ($x): void {
                try {
                    $s = $this->connection->prepare('INSERT INTO media.media_collections(id,property_id,last_changed_at,last_changed_at_offset,version) VALUES(:id,:property_id,:last_changed_at,:last_changed_at_offset,:version)');
                    $s->execute($this->root($x));
                } catch (PDOException $e) {
                    if ($e->getCode() === '23505') {
                        throw new MediaCollectionIdConflict;
                    } throw $e;
                }
            });
        } catch (MediaCollectionIdConflict $e) {
            throw $e;
        } catch (Throwable $error) {
            throw PersistentMediaCollectionIntegrity::invalid('write', $error);
        }
    }

    public function save(MediaCollection $collection, int $expectedVersion): void
    {
        $this->write($this->mapper->toSnapshot($collection), $expectedVersion, null);
    }

    public function saveWithMediaReservation(MediaCollection $collection, MediaId $mediaId, int $expectedVersion): void
    {
        $this->write($this->mapper->toSnapshot($collection), $expectedVersion, $mediaId);
    }

    private function write(MediaCollectionSnapshot $x, int $expected, ?MediaId $reservation): void
    {
        if ($x->version <= $expected) {
            throw new ConcurrentMediaCollectionModification;
        }
        try {
            $this->transaction->run(function () use ($x, $expected, $reservation): void {
                if ($reservation !== null) {
                    try {
                        $s = $this->connection->prepare('INSERT INTO media.media_id_reservations(media_id,collection_id) VALUES(:media_id,:collection_id)');
                        $s->execute(['media_id' => $reservation->value, 'collection_id' => $x->id]);
                    } catch (PDOException $e) {
                        if ($e->getCode() === '23505') {
                            throw new MediaIdConflict;
                        } throw $e;
                    }
                }
                $s = $this->connection->prepare('UPDATE media.media_collections SET property_id=:property_id,last_changed_at=:last_changed_at,last_changed_at_offset=:last_changed_at_offset,version=:version WHERE id=:id AND version=:expected');
                $s->execute($this->root($x) + ['expected' => $expected]);
                if ($s->rowCount() !== 1) {
                    throw new ConcurrentMediaCollectionModification;
                }
                $s = $this->connection->prepare('DELETE FROM media.media_items WHERE collection_id=:id');
                $s->execute(['id' => $x->id]);
                $s = $this->connection->prepare('INSERT INTO media.media_items(media_id,collection_id,type,checksum,media_order,caption,source,status,is_primary,added_at,added_at_offset,removed_at,removed_at_offset,archived_at,archived_at_offset) VALUES(:media_id,:collection_id,:type,:checksum,:media_order,:caption,:source,:status,:is_primary,:added_at,:added_at_offset,:removed_at,:removed_at_offset,:archived_at,:archived_at_offset)');
                foreach ($x->items as $item) {
                    $s->execute($this->item($item));
                }
            });
        } catch (MediaIdConflict|ConcurrentMediaCollectionModification $e) {
            throw $e;
        } catch (Throwable $error) {
            throw PersistentMediaCollectionIntegrity::invalid('write', $error);
        }
    }

    /** @return array<string,int|string> */
    private function root(MediaCollectionSnapshot $x): array
    {
        return ['id' => $x->id, 'property_id' => $x->propertyId, 'last_changed_at' => $x->lastChangedAt, 'last_changed_at_offset' => $this->offset($x->lastChangedAt), 'version' => $x->version];
    }

    /** @return array<string,int|string|null> */
    private function item(MediaItemSnapshot $i): array
    {
        return ['media_id' => $i->id, 'collection_id' => $i->collectionId, 'type' => $i->type, 'checksum' => $i->checksum, 'media_order' => $i->order, 'caption' => $i->caption, 'source' => $i->source, 'status' => $i->status, 'is_primary' => $i->primary ? 1 : 0, 'added_at' => $i->addedAt, 'added_at_offset' => $this->offset($i->addedAt), 'removed_at' => $i->removedAt, 'removed_at_offset' => $i->removedAt === null ? null : $this->offset($i->removedAt), 'archived_at' => $i->archivedAt, 'archived_at_offset' => $i->archivedAt === null ? null : $this->offset($i->archivedAt)];
    }

    /** @param array<string,mixed> $row */
    private function snapshot(array $row): MediaCollectionSnapshot
    {
        $s = $this->connection->prepare('SELECT * FROM media.media_items WHERE collection_id=:id ORDER BY media_order,media_id');
        $s->execute(['id' => (string) $row['id']]);
        $items = [];
        while (($i = $s->fetch(PDO::FETCH_ASSOC)) !== false) {
            $items[] = new MediaItemSnapshot((string) $i['media_id'], (string) $i['collection_id'], (string) $i['type'], (string) $i['checksum'], (int) $i['media_order'], $i['caption'] === null ? null : (string) $i['caption'], (string) $i['source'], (string) $i['status'], (bool) $i['is_primary'], $this->normal((string) $i['added_at'], (int) $i['added_at_offset']), $i['removed_at'] === null ? null : $this->normal((string) $i['removed_at'], (int) $i['removed_at_offset']), $i['archived_at'] === null ? null : $this->normal((string) $i['archived_at'], (int) $i['archived_at_offset']));
        }

        return new MediaCollectionSnapshot((string) $row['id'], (string) $row['property_id'], $this->normal((string) $row['last_changed_at'], (int) $row['last_changed_at_offset']), (int) $row['version'], $items);
    }

    private function normal(string $v, int $o): string
    {
        try {
            $sign = $o < 0 ? '-' : '+';
            $a = abs($o);

            return (new DateTimeImmutable($v))->setTimezone(new DateTimeZone(sprintf('%s%02d:%02d', $sign, intdiv($a, 60), $a % 60)))->format('Y-m-d\TH:i:s.uP');
        } catch (Throwable) {
            throw PersistentMediaCollectionIntegrity::invalid('date');
        }
    }

    private function offset(string $v): int
    {
        return intdiv((new DateTimeImmutable($v))->getOffset(), 60);
    }
}
