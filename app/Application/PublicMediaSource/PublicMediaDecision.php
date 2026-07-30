<?php

namespace App\Application\PublicMediaSource;

use App\Application\PublicMediaRevision\PublicMediaRevision;
use App\Application\PublicMediaRevision\PublicMediaRevisionChecksum;
use InvalidArgumentException;

final readonly class PublicMediaDecision
{
    /** @param list<PublicMediaItem> $gallery */
    public function __construct(
        public string $mediaCollectionId,
        public PublicMediaRevision $revision,
        public ?PublicMediaItem $cover,
        public array $gallery,
    ) {
        if (trim($mediaCollectionId) === '') {
            throw new InvalidArgumentException('A public Media decision requires a Media Collection identity.');
        }
        if (! $revision->checksum->equals(PublicMediaRevisionChecksum::fromCanonicalPayload($this->canonicalPayload()))) {
            throw new InvalidArgumentException('Public Media revision does not certify its content.');
        }
    }

    public function canonicalPayload(): string
    {
        return json_encode(
            [
                'cover' => $this->cover?->canonicalData(),
                'gallery' => array_map(static fn (PublicMediaItem $item): array => $item->canonicalData(), $this->gallery),
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }
}
