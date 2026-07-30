<?php

namespace Appart\Modules\Media\Domain\Model;

use Appart\Modules\Media\Domain\Event\AbstractMediaCollectionEvent;
use Appart\Modules\Media\Domain\Event\MediaAdded;
use Appart\Modules\Media\Domain\Event\MediaArchived;
use Appart\Modules\Media\Domain\Event\MediaCaptionChanged;
use Appart\Modules\Media\Domain\Event\MediaCollectionEvent;
use Appart\Modules\Media\Domain\Event\MediaMarkedPrimary;
use Appart\Modules\Media\Domain\Event\MediaRemoved;
use Appart\Modules\Media\Domain\Event\MediaReordered;
use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;
use Appart\Modules\Media\Domain\Exception\MediaCollectionViolation;
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

final class MediaCollection
{
    /** @var array<string, MediaItem> */
    private array $items = [];

    /** @var list<MediaCollectionEvent> */
    private array $events = [];

    private int $version = 0;

    private function __construct(private readonly MediaCollectionId $id, private readonly PropertyId $propertyId, private DateTimeImmutable $lastChangedAt) {}

    public static function create(MediaCollectionId $id, PropertyId $propertyId, DateTimeImmutable $at): self
    {
        return new self($id, $propertyId, $at);
    }

    /** @param list<MediaItem> $items */
    public static function reconstitute(MediaCollectionId $id, PropertyId $propertyId, DateTimeImmutable $lastChangedAt, array $items, int $version): self
    {
        if ($version < 0) {
            throw InvalidMediaValue::field('version');
        }
        $collection = new self($id, $propertyId, $lastChangedAt);
        foreach ($items as $item) {
            $collection->items[$item->id->value] = $item;
        }
        $collection->version = $version;

        return $collection;
    }

    public function add(MediaId $id, MediaType $type, MediaChecksum $checksum, MediaOrder $order, ?MediaCaption $caption, MediaSource $source, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        if (isset($this->items[$id->value])) {
            throw MediaCollectionViolation::duplicateMedia();
        }
        foreach ($this->items as $item) {
            if ($item->checksum->equals($checksum)) {
                throw MediaCollectionViolation::duplicateChecksum();
            }
            if ($item->isActive() && $item->order->equals($order)) {
                throw MediaCollectionViolation::duplicateOrder();
            }
        }
        $primary = $this->primary() === null;
        $this->items[$id->value] = new MediaItem($id, $this->id, $type, $checksum, $order, $caption, $source, MediaStatus::Active, $primary, $at);
        $this->recordEvent(new MediaAdded($this->id, $id, $type, $checksum, $order, $source, $primary, $at));
        if ($primary) {
            $this->recordEvent(new MediaMarkedPrimary($this->id, null, $id, $at));
        }
        $this->changed($at);
    }

    public function remove(MediaId $id, ?MediaId $replacementPrimaryId, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        $item = $this->activeItem($id);
        if ($item->primary) {
            $this->replacePrimary($id, $replacementPrimaryId, $at);
        } elseif ($replacementPrimaryId !== null) {
            throw MediaCollectionViolation::invalidPrimaryReplacement();
        }
        $this->items[$id->value] = $item->remove($at);
        $this->recordEvent(new MediaRemoved($this->id, $id, $at));
        $this->changed($at);
    }

    public function archive(MediaId $id, ?MediaId $replacementPrimaryId, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        $item = $this->activeItem($id);
        if ($item->primary) {
            $this->replacePrimary($id, $replacementPrimaryId, $at);
        } elseif ($replacementPrimaryId !== null) {
            throw MediaCollectionViolation::invalidPrimaryReplacement();
        }
        $this->items[$id->value] = $item->archive($at);
        $this->recordEvent(new MediaArchived($this->id, $id, $at));
        $this->changed($at);
    }

    /** @param list<MediaId> $orderedMediaIds */
    public function reorder(array $orderedMediaIds, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        $active = array_values(array_filter($this->items, static fn (MediaItem $item): bool => $item->isActive()));
        if (count($orderedMediaIds) !== count($active)) {
            throw MediaCollectionViolation::invalidReordering();
        }
        $seen = [];
        $changed = false;
        $orders = [];
        /** @var array<string, MediaOrder> $validatedOrders */
        $validatedOrders = [];
        foreach ($orderedMediaIds as $index => $mediaId) {
            if (isset($seen[$mediaId->value])) {
                throw MediaCollectionViolation::invalidReordering();
            }
            $item = $this->items[$mediaId->value] ?? null;
            if ($item === null || ! $item->isActive()) {
                throw MediaCollectionViolation::invalidReordering();
            }
            $seen[$mediaId->value] = true;
            $order = MediaOrder::fromInt($index + 1);
            $changed = $changed || ! $item->order->equals($order);
            $validatedOrders[$mediaId->value] = $order;
            $orders[$mediaId->value] = $order->value;
        }
        if (! $changed) {
            throw MediaCollectionViolation::unchangedOrder();
        }
        foreach ($validatedOrders as $mediaId => $order) {
            $this->items[$mediaId] = $this->items[$mediaId]->withOrder($order);
        }
        $this->recordEvent(new MediaReordered($this->id, $orders, $at));
        $this->changed($at);
    }

    public function markPrimary(MediaId $id, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        $item = $this->activeItem($id);
        $current = $this->primary();
        if ($item->primary) {
            throw MediaCollectionViolation::alreadyPrimary();
        }
        if ($current !== null) {
            $this->items[$current->id->value] = $current->withPrimary(false);
        }
        $this->items[$id->value] = $item->withPrimary(true);
        $this->recordEvent(new MediaMarkedPrimary($this->id, $current?->id, $id, $at));
        $this->changed($at);
    }

    public function changeCaption(MediaId $id, MediaCaption $caption, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        $item = $this->activeItem($id);
        if ($item->caption?->equals($caption) === true) {
            throw MediaCollectionViolation::unchangedCaption();
        }
        $this->items[$id->value] = $item->withCaption($caption);
        $this->recordEvent(new MediaCaptionChanged($this->id, $id, $item->caption, $caption, $at));
        $this->changed($at);
    }

    public function id(): MediaCollectionId
    {
        return $this->id;
    }

    public function propertyId(): PropertyId
    {
        return $this->propertyId;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function lastChangedAt(): DateTimeImmutable
    {
        return $this->lastChangedAt;
    }

    /** @return list<MediaItem> */
    public function items(): array
    {
        return array_values($this->items);
    }

    public function primary(): ?MediaItem
    {
        foreach ($this->items as $item) {
            if ($item->isActive() && $item->primary) {
                return $item;
            }
        }

        return null;
    }

    /** @return list<MediaCollectionEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function activeItem(MediaId $id): MediaItem
    {
        $item = $this->items[$id->value] ?? throw MediaCollectionViolation::mediaNotFound();
        if (! $item->isActive()) {
            throw MediaCollectionViolation::inactiveMedia();
        }

        return $item;
    }

    private function replacePrimary(MediaId $removedId, ?MediaId $replacementId, DateTimeImmutable $at): void
    {
        if ($replacementId === null) {
            throw MediaCollectionViolation::primaryReplacementRequired();
        }
        if ($replacementId->equals($removedId)) {
            throw MediaCollectionViolation::invalidPrimaryReplacement();
        }
        $replacement = $this->activeItem($replacementId);
        $removed = $this->activeItem($removedId);
        $this->items[$removedId->value] = $removed->withPrimary(false);
        $this->items[$replacementId->value] = $replacement->withPrimary(true);
        $this->recordEvent(new MediaMarkedPrimary($this->id, $removedId, $replacementId, $at));
    }

    private function guardTime(DateTimeImmutable $at): void
    {
        if ($at < $this->lastChangedAt) {
            throw InvalidMediaValue::field('event_time');
        }
    }

    private function changed(DateTimeImmutable $at): void
    {
        $this->lastChangedAt = $at;
        $this->version++;
    }

    private function recordEvent(MediaCollectionEvent $event): void
    {
        if ($event instanceof AbstractMediaCollectionEvent) {
            $event->stamp($this->version + 1, count($this->events) + 1);
        }
        $this->events[] = $event;
    }
}
