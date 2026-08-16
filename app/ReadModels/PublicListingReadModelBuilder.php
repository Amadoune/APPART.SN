<?php

namespace App\ReadModels;

use App\Projections\SearchListingProjection;
use App\Projections\SeoListingProjection;

final readonly class PublicListingReadModelBuilder
{
    public function build(?SearchListingProjection $search, ?SeoListingProjection $seo): ?PublicListingReadModel
    {
        if ($search === null || $seo === null) {
            return null;
        }

        $this->assertConsistent($search, $seo);

        return new PublicListingReadModel(
            listingId: $search->listingId,
            propertyId: $search->propertyId,
            mediaCollectionId: $search->mediaCollectionId,
            headline: $seo->headline,
            description: $seo->description,
            propertyType: $search->propertyType,
            surfaceSquareMeters: $search->surfaceSquareMeters,
            roomCount: $search->roomCount,
            geographicPlaceId: $search->geographicPlaceId,
            primaryMediaId: $search->primaryMediaId,
            publicMediaUrl: $seo->publicMediaUrl,
            listingStatus: $search->listingStatus,
            publishedAt: $search->publishedAt,
            expiresAt: $search->expiresAt,
            canonicalUrl: $seo->canonicalUrl,
            canonicalHistory: $seo->canonicalHistory,
            indexability: $seo->indexability,
            robots: $seo->robots,
            htmlRobotsDirective: $seo->htmlRobotsDirective,
            breadcrumb: $seo->breadcrumb,
            structuredData: $seo->structuredData,
            publicJsonLd: $seo->publicJsonLd,
            decidedAt: $seo->decidedAt,
            expiredListingTreatment: $seo->expiredListingTreatment,
            transactionKind: $search->transactionKind,
            city: $this->city($seo->breadcrumb, $seo->geographyBreadcrumb),
            breadcrumbSchemaVersion: $seo->breadcrumbSchemaVersion,
            geographyBreadcrumb: $seo->geographyBreadcrumb,
        );
    }

    /**
     * @param  list<array{label:string, url:string}>  $breadcrumb
     * @param  list<array{placeId:string, type:string, label:string}>  $geographyBreadcrumb
     */
    private function city(array $breadcrumb, array $geographyBreadcrumb): ?string
    {
        foreach ($geographyBreadcrumb as $item) {
            if ($item['type'] === 'city') {
                return $item['label'];
            }
        }
        if ($breadcrumb === []) {
            return null;
        }

        $cityIndex = count($breadcrumb) > 1 ? array_key_last($breadcrumb) - 1 : array_key_last($breadcrumb);
        $last = $breadcrumb[$cityIndex];

        return $last['label'] === '' ? null : $last['label'];
    }

    private function assertConsistent(SearchListingProjection $search, SeoListingProjection $seo): void
    {
        $datesMatch = ($seo->publishedAt === null || $seo->publishedAt == $search->publishedAt)
            && ($seo->expiresAt === null || $seo->expiresAt == $search->expiresAt);
        $publicStateIsCoherent = $search->listingStatus === 'published'
            && (($seo->indexability === 'indexable' && $seo->robots === 'index_follow' && $seo->htmlRobotsDirective === 'index, follow' && $seo->publicJsonLd !== null)
                || ($seo->indexability === 'not_indexable' && $seo->robots === 'noindex_follow' && $seo->htmlRobotsDirective === 'noindex, follow' && $seo->publicJsonLd === null));

        if ($search->listingId !== $seo->listingId || ! $datesMatch || ! $publicStateIsCoherent || $seo->expiredListingTreatment === 'remove') {
            throw new InconsistentPublicListingProjection('Search and SEO listing projections are structurally inconsistent.');
        }
    }
}
