<?php

namespace App\Application\PublicMediaSource;

use InvalidArgumentException;

final readonly class PublicMediaSourceRevisionV2
{
    /**
     * @param  list<array{mediaId:string,aggregateVersion:int,intentChecksum:string}>  $attachments
     * @param  list<array{mediaId:string,assetVersion:int,state:string,contentChecksum:string}>  $assets
     */
    public function __construct(
        public string $listingId,
        public int $publicationVersion,
        public string $listingState,
        public string $mediaCollectionId,
        public int $collectionVersion,
        public array $attachments,
        public array $assets,
    ) {
        if ($publicationVersion < 1 || $collectionVersion < 0 || $listingState === '') {
            throw new InvalidArgumentException('Invalid Public Media source revision.');
        }
    }

    /** @return array{listing:array{listingId:string,publicationVersion:int,state:string},collection:array{mediaCollectionId:string,collectionVersion:int},attachments:list<array{mediaId:string,aggregateVersion:int,intentChecksum:string}>,assets:list<array{mediaId:string,assetVersion:int,state:string,contentChecksum:string}>} */
    public function canonicalData(): array
    {
        return [
            'listing' => ['listingId' => $this->listingId, 'publicationVersion' => $this->publicationVersion, 'state' => $this->listingState],
            'collection' => ['mediaCollectionId' => $this->mediaCollectionId, 'collectionVersion' => $this->collectionVersion],
            'attachments' => array_map(static fn (array $item): array => [
                'mediaId' => $item['mediaId'],
                'aggregateVersion' => $item['aggregateVersion'],
                'intentChecksum' => $item['intentChecksum'],
            ], $this->attachments),
            'assets' => array_map(static fn (array $item): array => [
                'mediaId' => $item['mediaId'],
                'assetVersion' => $item['assetVersion'],
                'state' => $item['state'],
                'contentChecksum' => $item['contentChecksum'],
            ], $this->assets),
        ];
    }

    public function checksum(): string
    {
        return hash('sha256', json_encode($this->canonicalData(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function compareTo(self $other): PublicMediaSourceRevisionRelationV2
    {
        $current = [$this->publicationVersion, $this->collectionVersion, ...array_column($this->attachments, 'aggregateVersion'), ...array_column($this->assets, 'assetVersion')];
        $previous = [$other->publicationVersion, $other->collectionVersion, ...array_column($other->attachments, 'aggregateVersion'), ...array_column($other->assets, 'assetVersion')];
        if ($this->checksum() === $other->checksum()) {
            return PublicMediaSourceRevisionRelationV2::Equal;
        }
        if (count($current) !== count($previous)) {
            return $this->collectionVersion >= $other->collectionVersion ? PublicMediaSourceRevisionRelationV2::Newer : PublicMediaSourceRevisionRelationV2::Older;
        }
        $hasNewer = $hasOlder = false;
        foreach ($current as $index => $version) {
            $hasNewer = $hasNewer || $version > $previous[$index];
            $hasOlder = $hasOlder || $version < $previous[$index];
        }

        return match (true) {
            $hasNewer && ! $hasOlder => PublicMediaSourceRevisionRelationV2::Newer,
            $hasOlder && ! $hasNewer => PublicMediaSourceRevisionRelationV2::Older,
            default => PublicMediaSourceRevisionRelationV2::Incomparable,
        };
    }
}
