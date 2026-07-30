<?php

namespace App\Application\PublicMediaSource;

use InvalidArgumentException;

final readonly class PublicMediaItem
{
    /** @param list<PublicMediaVariant> $variants */
    public function __construct(public string $mediaId, public string $url, public array $variants)
    {
        if (trim($mediaId) === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid public Media item.');
        }
    }

    /** @return array{mediaId:string,url:string,variants:list<array{name:string,url:string}>} */
    public function canonicalData(): array
    {
        return [
            'mediaId' => $this->mediaId,
            'url' => $this->url,
            'variants' => array_map(
                static fn (PublicMediaVariant $variant): array => ['name' => $variant->name, 'url' => $variant->url],
                $this->variants,
            ),
        ];
    }
}
