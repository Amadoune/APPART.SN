<?php

namespace Appart\Modules\Media\Domain\Exception;

final class MediaCollectionViolation extends MediaException
{
    public static function duplicateMedia(): self
    {
        return new self('The media identity already exists in this collection.');
    }

    public static function duplicateChecksum(): self
    {
        return new self('The media checksum already exists in this collection.');
    }

    public static function duplicateOrder(): self
    {
        return new self('The media order is already used in this collection.');
    }

    public static function mediaNotFound(): self
    {
        return new self('The media does not belong to this collection.');
    }

    public static function inactiveMedia(): self
    {
        return new self('Only active media can be modified.');
    }

    public static function primaryReplacementRequired(): self
    {
        return new self('The primary media requires an explicit active replacement.');
    }

    public static function invalidPrimaryReplacement(): self
    {
        return new self('The primary replacement is invalid.');
    }

    public static function invalidReordering(): self
    {
        return new self('Reordering must contain every active media exactly once.');
    }

    public static function alreadyPrimary(): self
    {
        return new self('The media is already primary.');
    }

    public static function unchangedOrder(): self
    {
        return new self('The active media order is unchanged.');
    }

    public static function unchangedCaption(): self
    {
        return new self('The media caption is unchanged.');
    }
}
