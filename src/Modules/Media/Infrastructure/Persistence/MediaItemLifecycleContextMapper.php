<?php

namespace Appart\Modules\Media\Infrastructure\Persistence;

use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppend;

final readonly class MediaItemLifecycleContextMapper
{
    /** @return array<string, int|string|null> */
    public function map(MediaItemLifecycleContextualAppend $append): array
    {
        return [
            'media_id' => $append->mediaId->value,
            'version' => $append->nextVersion(),
            'contract_version' => $append->context->contractVersion->value,
            'collection_id' => $append->context->collectionId->value,
            'collection_version' => $append->context->collectionVersion->value,
            'actor_id' => $append->context->actor->value,
            'occurred_at' => $append->context->occurredAt->canonical(),
            'primary_disposition' => $append->context->collectionDecision->disposition->value,
            'replacement_media_id' => $append->context->collectionDecision->replacementMediaId->value ?? null,
            'context_checksum' => $append->context->checksum()->value,
        ];
    }
}
