<?php

namespace App\Application\PublicMediaSource;

use InvalidArgumentException;

final readonly class PublicMediaItemV2
{
    public function __construct(
        public string $mediaId,
        public string $publicLocator,
        public int $deliveryRevision,
        public int $order,
        public bool $primary,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $mediaId) !== 1
            || $publicLocator !== '/media/'.$mediaId.'/revisions/'.$deliveryRevision
            || $deliveryRevision < 1
            || $order < 1) {
            throw new InvalidArgumentException('Invalid PublicMediaItemV2.');
        }
    }

    /** @return array{mediaId:string,publicLocator:string,deliveryRevision:int,order:int,primary:bool} */
    public function canonicalData(): array
    {
        return [
            'mediaId' => $this->mediaId,
            'publicLocator' => $this->publicLocator,
            'deliveryRevision' => $this->deliveryRevision,
            'order' => $this->order,
            'primary' => $this->primary,
        ];
    }
}
