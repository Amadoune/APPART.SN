<?php

namespace Tests\Unit\Modules\ContentSeo;

use Appart\Modules\ContentSeo\Domain\Model\PublicJsonLd;
use Appart\Modules\ContentSeo\Domain\Model\StructuredData;
use Appart\Modules\ContentSeo\Domain\ValueObject\PublicMediaUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\StructuredDataType;
use PHPUnit\Framework\TestCase;

final class PublicJsonLdTest extends TestCase
{
    public function test_public_json_ld_contains_only_validated_public_facts_and_schema_metadata(): void
    {
        $jsonLd = PublicJsonLd::fromStructuredData($this->structuredData(), PublicMediaUrl::fromString('https://media.appart.sn/listings/primary.webp'));

        self::assertSame([
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateListing',
            'name' => 'Appartement moderne a Dakar | APPART.SN',
            'url' => 'https://appart.sn/annonces/appartement-moderne-dakar',
            'category' => 'Appartement',
            'addressLocality' => 'Dakar',
            'image' => 'https://media.appart.sn/listings/primary.webp',
        ], $jsonLd->document);
        self::assertSame($jsonLd->document, json_decode($jsonLd->json, true, flags: JSON_THROW_ON_ERROR));
    }

    public function test_generation_is_deterministic_and_does_not_mutate_its_source(): void
    {
        $source = $this->structuredData();
        $before = serialize($source);

        $first = PublicJsonLd::fromStructuredData($source, null);
        $second = PublicJsonLd::fromStructuredData($source, null);

        self::assertEquals($first, $second);
        self::assertSame($first->json, $second->json);
        self::assertArrayNotHasKey('image', $first->document);
        self::assertSame($before, serialize($source));
    }

    private function structuredData(): StructuredData
    {
        return new StructuredData(StructuredDataType::RealEstateListing, [
            'url' => 'https://appart.sn/annonces/appartement-moderne-dakar',
            'name' => 'Appartement moderne a Dakar | APPART.SN',
            'addressLocality' => 'Dakar',
            'category' => 'Appartement',
        ]);
    }
}
