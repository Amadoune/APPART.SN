<?php

namespace Appart\Modules\Media\Application\Attachment\Contract;

use Appart\Modules\Media\Application\Attachment\MediaAttachmentIntent;

interface MediaAttachmentIntentStore
{
    public function find(string $intentId): ?MediaAttachmentIntent;

    public function reserve(MediaAttachmentIntent $intent): bool;

    public function markApplied(string $intentId, int $aggregateVersion): void;
}
