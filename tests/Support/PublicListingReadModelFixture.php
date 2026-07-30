<?php

namespace Tests\Support;

use App\ReadModels\PublicListingReadModel;
use DateTimeImmutable;

final class PublicListingReadModelFixture
{
    public static function make(bool $indexable = true, ?string $listingId = null, ?string $canonicalUrl = null): PublicListingReadModel
    {
        $publishedAt = new DateTimeImmutable('2026-07-18T10:01:00+00:00');
        $canonical = $canonicalUrl ?? 'https://appart.sn/annonces/appartement-moderne-dakar';
        $listingId ??= '91000000-0000-4000-8000-000000000001';
        $jsonLd = json_encode(['@context' => 'https://schema.org', '@type' => 'RealEstateListing', 'name' => 'Appartement moderne a Dakar | APPART.SN', 'url' => $canonical, 'category' => 'Appartement', 'addressLocality' => 'Dakar', 'image' => 'https://media.appart.sn/listings/primary.webp'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return new PublicListingReadModel(
            listingId: $listingId,
            propertyId: '92000000-0000-4000-8000-000000000001',
            mediaCollectionId: '93000000-0000-4000-8000-000000000001',
            headline: 'Appartement moderne a Dakar | APPART.SN',
            description: 'Decouvrez cet appartement moderne, lumineux et bien situe au coeur de Dakar pour votre prochain logement.',
            propertyType: 'Appartement',
            surfaceSquareMeters: 120,
            roomCount: 5,
            geographicPlaceId: 'place:dakar:plateau',
            primaryMediaId: '93000000-0000-4000-8000-000000000101',
            publicMediaUrl: 'https://media.appart.sn/listings/primary.webp',
            listingStatus: 'published',
            publishedAt: $publishedAt,
            expiresAt: new DateTimeImmutable('2026-11-15T10:00:00+00:00'),
            canonicalUrl: $canonical,
            canonicalHistory: [[
                'url' => $canonical,
                'disposition' => 'current',
                'effectiveAt' => $publishedAt,
                'replacedAt' => null,
                'redirectTarget' => null,
            ]],
            indexability: $indexable ? 'indexable' : 'not_indexable',
            robots: $indexable ? 'index_follow' : 'noindex_follow',
            htmlRobotsDirective: $indexable ? 'index, follow' : 'noindex, follow',
            breadcrumb: [
                ['label' => 'Accueil', 'url' => 'https://appart.sn/accueil'],
                ['label' => 'Dakar', 'url' => 'https://appart.sn/dakar'],
                ['label' => 'Appartement moderne a Dakar', 'url' => $canonical],
            ],
            structuredData: $indexable ? ['type' => 'real_estate_listing', 'facts' => ['addressLocality' => 'Dakar', 'category' => 'Appartement', 'name' => 'Appartement moderne a Dakar | APPART.SN', 'url' => $canonical]] : null,
            publicJsonLd: $indexable ? $jsonLd : null,
            decidedAt: new DateTimeImmutable('2026-07-18T10:02:00+00:00'),
            expiredListingTreatment: 'not_applicable',
        );
    }
}
