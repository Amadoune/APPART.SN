<?php

namespace Tests\Support;

use App\Application\PublicMediaRevision\PublicMediaRevisionStrategy;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaItem;
use App\Application\PublicMediaSource\PublicMediaVariant;

final readonly class PublicMediaDecisionFixture
{
    public static function make(int $version = 1, string $coverId = 'media:cover'): PublicMediaDecision
    {
        $cover = new PublicMediaItem($coverId, 'https://media.appart.sn/cover.webp', [
            new PublicMediaVariant('thumbnail', 'https://media.appart.sn/cover-thumbnail.webp'),
        ]);
        $gallery = [
            $cover,
            new PublicMediaItem('media:gallery-2', 'https://media.appart.sn/gallery-2.webp', []),
        ];
        $payload = json_encode(
            ['cover' => $cover->canonicalData(), 'gallery' => array_map(static fn (PublicMediaItem $item): array => $item->canonicalData(), $gallery)],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        return new PublicMediaDecision(
            'media-collection:listing-1',
            (new PublicMediaRevisionStrategy)->revise($version, $payload, 'media-collection:'.$version.':published'),
            $cover,
            $gallery,
        );
    }
}
