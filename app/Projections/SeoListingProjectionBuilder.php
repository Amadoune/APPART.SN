<?php

namespace App\Projections;

use Appart\Modules\ContentSeo\Domain\Model\BreadcrumbItem;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoDecision;
use Appart\Modules\ContentSeo\Domain\Model\PublicGeographyBreadcrumbItemV2;
use Appart\Modules\ContentSeo\Domain\ValueObject\ExpiredListingTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoIndexability;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoPageTreatment;
use LogicException;

final readonly class SeoListingProjectionBuilder
{
    public function build(ListingSeoDecision $decision): ?SeoListingProjection
    {
        $this->assertFactoryInvariants($decision);

        if ($decision->pageTreatment === SeoPageTreatment::Remove) {
            return null;
        }

        return new SeoListingProjection(
            listingId: $decision->listingId->value,
            canonicalUrl: $decision->canonical->value,
            canonicalHistory: array_map(static fn (CanonicalHistoryEntry $entry): array => [
                'url' => $entry->canonical->value,
                'disposition' => $entry->disposition->value,
                'effectiveAt' => $entry->effectiveAt,
                'replacedAt' => $entry->replacedAt,
                'redirectTarget' => $entry->redirectTarget?->value,
            ], $decision->canonicalHistory),
            headline: $decision->headline?->value,
            description: $decision->description?->value,
            indexability: $decision->indexability->value,
            robots: $decision->robots->value,
            htmlRobotsDirective: $decision->htmlRobotsDirective->value,
            breadcrumb: array_map(static fn (BreadcrumbItem $item): array => [
                'label' => $item->label,
                'url' => $item->url->value,
            ], $decision->breadcrumb),
            structuredData: $decision->structuredData === null ? null : [
                'type' => $decision->structuredData->type->value,
                'facts' => $decision->structuredData->facts,
            ],
            publicJsonLd: $decision->publicJsonLd?->json,
            publicMediaUrl: $decision->publicMedia?->value,
            publishedAt: $decision->publishedAt,
            expiresAt: $decision->expiresAt,
            decidedAt: $decision->decidedAt,
            expiredListingTreatment: $decision->expiredTreatment->value,
            breadcrumbSchemaVersion: $decision->geographyBreadcrumbV2 === [] ? 'content-seo-breadcrumb-v1' : 'public-geography-breadcrumb-v2',
            geographyBreadcrumb: array_map(static fn (PublicGeographyBreadcrumbItemV2 $item): array => [
                'placeId' => $item->placeId,
                'type' => $item->type,
                'label' => $item->label,
            ], $decision->geographyBreadcrumbV2),
        );
    }

    private function assertFactoryInvariants(ListingSeoDecision $decision): void
    {
        $indexable = $decision->indexability === SeoIndexability::Indexable;
        if (($indexable && ($decision->robots !== RobotsPolicy::IndexFollow || $decision->htmlRobotsDirective->value !== 'index, follow' || $decision->publicJsonLd === null || $decision->pageTreatment !== SeoPageTreatment::Retain))
            || (! $indexable && ($decision->robots !== RobotsPolicy::NoIndexFollow || $decision->htmlRobotsDirective->value !== 'noindex, follow' || $decision->publicJsonLd !== null))
            || ($decision->expiredTreatment === ExpiredListingTreatment::Remove && $decision->pageTreatment !== SeoPageTreatment::Remove)
            || ($decision->expiredTreatment === ExpiredListingTreatment::RetainNoIndex && ($indexable || $decision->pageTreatment !== SeoPageTreatment::Retain))) {
            throw new LogicException('Listing SEO decision violates its factory invariants.');
        }
    }
}
