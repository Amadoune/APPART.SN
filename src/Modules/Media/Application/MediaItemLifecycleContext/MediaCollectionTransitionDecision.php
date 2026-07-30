<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

use Appart\Modules\Media\Domain\ValueObject\MediaId;
use InvalidArgumentException;

final readonly class MediaCollectionTransitionDecision
{
    private function __construct(
        public MediaPrimaryTransitionDisposition $disposition,
        public ?MediaId $replacementMediaId,
    ) {}

    public static function notPrimary(): self
    {
        return new self(MediaPrimaryTransitionDisposition::NotPrimary, null);
    }

    public static function replacementSelected(MediaId $transitionedMediaId, MediaId $replacementMediaId): self
    {
        if ($transitionedMediaId->equals($replacementMediaId)) {
            throw new InvalidArgumentException('The replacement media must differ from the transitioned media.');
        }

        return new self(MediaPrimaryTransitionDisposition::ReplacementSelected, $replacementMediaId);
    }
}
