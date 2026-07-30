<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\ValueObject\PublicMediaUrl;

final readonly class PublicJsonLd
{
    /** @param array<string, string> $document */
    private function __construct(public array $document, public string $json) {}

    public static function fromStructuredData(StructuredData $structuredData, ?PublicMediaUrl $media): self
    {
        $facts = $structuredData->facts;
        $document = [
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateListing',
            'name' => $facts['name'],
            'url' => $facts['url'],
            'category' => $facts['category'],
            'addressLocality' => $facts['addressLocality'],
        ];
        if ($media !== null) {
            $document['image'] = $media->value;
        }

        return new self($document, json_encode($document, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
