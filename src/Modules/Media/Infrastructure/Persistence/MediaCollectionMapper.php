<?php

namespace Appart\Modules\Media\Infrastructure\Persistence;

use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\Model\MediaItem;
use Appart\Modules\Media\Domain\ValueObject\MediaCaption;
use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaSource;
use Appart\Modules\Media\Domain\ValueObject\MediaStatus;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use DateTimeImmutable;
use Throwable;

final class MediaCollectionMapper
{
    public function toSnapshot(MediaCollection $collection): MediaCollectionSnapshot
    {
        return new MediaCollectionSnapshot(
            $collection->id()->value,
            $collection->propertyId()->value,
            $this->date($collection->lastChangedAt()),
            $collection->version(),
            array_map(fn (MediaItem $item): MediaItemSnapshot => new MediaItemSnapshot(
                $item->id->value, $item->collectionId->value, $item->type->value,
                $item->checksum->value, $item->order->value, $item->caption?->value,
                $item->source->value, $item->status->value, $item->primary,
                $this->date($item->addedAt), $item->removedAt === null ? null : $this->date($item->removedAt),
                $item->archivedAt === null ? null : $this->date($item->archivedAt),
            ), $collection->items()),
        );
    }

    public function toAggregate(MediaCollectionSnapshot $snapshot): MediaCollection
    {
        try {
            if ($snapshot->version < 0) {
                throw PersistentMediaCollectionIntegrity::invalid('version');
            }
            $collectionId = MediaCollectionId::fromString($snapshot->id);
            $seenIds = $seenChecksums = $activeOrders = [];
            $primaryCount = 0;
            $items = [];
            foreach ($snapshot->items as $item) {
                if ($item->collectionId !== $snapshot->id || isset($seenIds[$item->id]) || isset($seenChecksums[$item->checksum])) {
                    throw PersistentMediaCollectionIntegrity::invalid('items');
                }
                $status = MediaStatus::tryFrom($item->status) ?? throw PersistentMediaCollectionIntegrity::invalid('status');
                if ($status === MediaStatus::Active && isset($activeOrders[$item->order])) {
                    throw PersistentMediaCollectionIntegrity::invalid('order');
                }
                $seenIds[$item->id] = $seenChecksums[$item->checksum] = true;
                if ($status === MediaStatus::Active) {
                    $activeOrders[$item->order] = true;
                }
                $primaryCount += $item->primary ? 1 : 0;
                if ($primaryCount > 1) {
                    throw PersistentMediaCollectionIntegrity::invalid('primary');
                }
                $items[] = new MediaItem(
                    MediaId::fromString($item->id), $collectionId,
                    MediaType::tryFrom($item->type) ?? throw PersistentMediaCollectionIntegrity::invalid('type'),
                    MediaChecksum::fromSha256($item->checksum), MediaOrder::fromInt($item->order),
                    $item->caption === null ? null : MediaCaption::fromString($item->caption),
                    MediaSource::tryFrom($item->source) ?? throw PersistentMediaCollectionIntegrity::invalid('source'),
                    $status, $item->primary, $this->parseDate($item->addedAt),
                    $item->removedAt === null ? null : $this->parseDate($item->removedAt),
                    $item->archivedAt === null ? null : $this->parseDate($item->archivedAt),
                );
            }

            return MediaCollection::reconstitute(
                $collectionId, PropertyId::fromString($snapshot->propertyId),
                $this->parseDate($snapshot->lastChangedAt), $items, $snapshot->version,
            );
        } catch (PersistentMediaCollectionIntegrity $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentMediaCollectionIntegrity::invalid('snapshot');
        }
    }

    private function date(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d\TH:i:s.uP');
    }

    private function parseDate(string $date): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s.uP', $date);
        $errors = DateTimeImmutable::getLastErrors();
        if ($parsed === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $this->date($parsed) !== $date) {
            throw PersistentMediaCollectionIntegrity::invalid('date');
        }

        return $parsed;
    }
}
