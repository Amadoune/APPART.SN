<?php

namespace App\Application\PublicMediaSource;

use App\Application\PublicMediaRevision\PublicMediaRevision;
use App\Application\PublicMediaRevision\PublicMediaRevisionChecksum;
use InvalidArgumentException;

final readonly class PublicMediaDecision
{
    /**
     * @param  list<PublicMediaItem>  $gallery
     * @param  list<PublicMediaItemV2>  $itemsV2
     */
    public function __construct(
        public string $mediaCollectionId,
        public PublicMediaRevision $revision,
        public ?PublicMediaItem $cover,
        public array $gallery,
        public int $schemaVersion = 1,
        public array $itemsV2 = [],
        public ?PublicMediaSourceRevisionV2 $sourceRevisionV2 = null,
    ) {
        if (trim($mediaCollectionId) === '') {
            throw new InvalidArgumentException('A public Media decision requires a Media Collection identity.');
        }
        if ($schemaVersion === 2 && ($itemsV2 === [] || $sourceRevisionV2 === null || count(array_filter($itemsV2, static fn (PublicMediaItemV2 $item): bool => $item->primary)) !== 1)) {
            throw new InvalidArgumentException('A V2 public Media decision requires items, one primary and a source revision.');
        }
        if (! in_array($schemaVersion, [1, 2], true)) {
            throw new InvalidArgumentException('Unknown public Media decision schema.');
        }
        if (! $revision->checksum->equals(PublicMediaRevisionChecksum::fromCanonicalPayload($this->canonicalPayload()))) {
            throw new InvalidArgumentException('Public Media revision does not certify its content.');
        }
    }

    public function canonicalPayload(): string
    {
        if ($this->schemaVersion === 2) {
            return json_encode(
                [
                    'schemaVersion' => 2,
                    'sourceRevision' => $this->sourceRevisionV2?->canonicalData(),
                    'sourceRevisionChecksum' => $this->sourceRevisionV2?->checksum(),
                    'items' => array_map(static fn (PublicMediaItemV2 $item): array => $item->canonicalData(), $this->itemsV2),
                ],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            );
        }

        return json_encode(
            [
                'cover' => $this->cover?->canonicalData(),
                'gallery' => array_map(static fn (PublicMediaItem $item): array => $item->canonicalData(), $this->gallery),
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }
}
