<?php

namespace Appart\Modules\Media\Domain\Model;

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
use DateTimeImmutable;

final readonly class MediaItem
{
    public function __construct(public MediaId $id, public MediaCollectionId $collectionId, public MediaType $type, public MediaChecksum $checksum, public MediaOrder $order, public ?MediaCaption $caption, public MediaSource $source, public MediaStatus $status, public bool $primary, public DateTimeImmutable $addedAt, public ?DateTimeImmutable $removedAt = null, public ?DateTimeImmutable $archivedAt = null)
    {
        if ($removedAt !== null && $archivedAt !== null) {
            throw InvalidMediaValue::field('terminal_dates');
        }
        if ($status === MediaStatus::Active && ($removedAt !== null || $archivedAt !== null)) {
            throw InvalidMediaValue::field('active_terminal_date');
        }
        if ($status === MediaStatus::Removed && $removedAt === null) {
            throw InvalidMediaValue::field('removed_state');
        }
        if ($status === MediaStatus::Archived && ($archivedAt === null || $primary)) {
            throw InvalidMediaValue::field('archived_state');
        }
        if ($status !== MediaStatus::Active && $primary) {
            throw InvalidMediaValue::field('terminal_primary');
        }
        if (($removedAt !== null && $removedAt < $addedAt) || ($archivedAt !== null && $archivedAt < $addedAt)) {
            throw InvalidMediaValue::field('terminal_time');
        }
    }

    public function isActive(): bool
    {
        return $this->status === MediaStatus::Active;
    }

    public function withOrder(MediaOrder $order): self
    {
        $this->guardActive();

        return new self($this->id, $this->collectionId, $this->type, $this->checksum, $order, $this->caption, $this->source, $this->status, $this->primary, $this->addedAt, $this->removedAt, $this->archivedAt);
    }

    public function withCaption(MediaCaption $caption): self
    {
        $this->guardActive();

        return new self($this->id, $this->collectionId, $this->type, $this->checksum, $this->order, $caption, $this->source, $this->status, $this->primary, $this->addedAt, $this->removedAt, $this->archivedAt);
    }

    public function withPrimary(bool $primary): self
    {
        $this->guardActive();

        return new self($this->id, $this->collectionId, $this->type, $this->checksum, $this->order, $this->caption, $this->source, $this->status, $primary, $this->addedAt, $this->removedAt, $this->archivedAt);
    }

    public function remove(DateTimeImmutable $at): self
    {
        $this->guardTerminalTransition($at);

        return new self($this->id, $this->collectionId, $this->type, $this->checksum, $this->order, $this->caption, $this->source, MediaStatus::Removed, false, $this->addedAt, $at, null);
    }

    public function archive(DateTimeImmutable $at): self
    {
        $this->guardTerminalTransition($at);

        return new self($this->id, $this->collectionId, $this->type, $this->checksum, $this->order, $this->caption, $this->source, MediaStatus::Archived, false, $this->addedAt, null, $at);
    }

    private function guardActive(): void
    {
        if (! $this->isActive()) {
            throw MediaCollectionViolation::inactiveMedia();
        }
    }

    private function guardTerminalTransition(DateTimeImmutable $at): void
    {
        $this->guardActive();
        if ($at < $this->addedAt) {
            throw InvalidMediaValue::field('terminal_time');
        }
    }
}
