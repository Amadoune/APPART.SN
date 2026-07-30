<?php

namespace Appart\Modules\ContentSeo\Domain\Policy;

use Appart\Modules\ContentSeo\Domain\Exception\InconsistentSeoSources;
use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;
use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\Model\BreadcrumbItem;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoDecision;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PublicGeographySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PublicJsonLd;
use Appart\Modules\ContentSeo\Domain\Model\PublicMediaSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\StructuredData;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ExpiredListingTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\HtmlRobotsDirective;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\MetaDescription;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoIndexability;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoPageTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoTitle;
use Appart\Modules\ContentSeo\Domain\ValueObject\StructuredDataType;
use DateTimeImmutable;

final readonly class ListingSeoDecisionPolicy
{
    public function __construct(private CanonicalPolicy $canonicals, private CanonicalHistoryPolicy $history) {}

    /** @param list<CanonicalHistoryEntry> $canonicalHistory */
    public function decide(
        ListingSeoSource $listing,
        SearchSeoSource $search,
        PropertySeoSource $property,
        ?PublicGeographySeoSource $geography,
        ?PublicMediaSeoSource $media,
        array $canonicalHistory,
        DateTimeImmutable $at,
    ): ListingSeoDecision {
        $this->assertSameListing($listing, $search, $property, $geography, $media);
        $canonical = $this->canonicals->fromPath($listing->canonicalPath);
        $canonicalHistory = $this->canonicalHistory($canonical, $canonicalHistory, $at);
        [$title, $description] = $this->publicContent($listing);
        $datesAreCoherent = $listing->publishedAt !== null && $listing->expiresAt !== null && $listing->expiresAt > $listing->publishedAt;
        $indexable = $listing->state === ListingSeoState::Published
            && $search->state === SearchSeoState::Public
            && $property->state === PropertySeoState::Available
            && $geography !== null
            && $media !== null
            && $title !== null
            && $description !== null
            && $datesAreCoherent;

        $breadcrumb = $title !== null && $geography !== null
            ? [...$geography->breadcrumb, new BreadcrumbItem($listing->headline, $canonical)]
            : [];
        $structuredData = $title !== null && $geography !== null
            ? new StructuredData(StructuredDataType::RealEstateListing, [
                'name' => $title->value,
                'url' => $canonical->value,
                'category' => $property->propertyType,
                'addressLocality' => $geography->locality,
            ])
            : null;
        $robots = $indexable ? RobotsPolicy::IndexFollow : RobotsPolicy::NoIndexFollow;
        $publicJsonLd = $indexable && $structuredData !== null ? PublicJsonLd::fromStructuredData($structuredData, $media->url) : null;

        return ListingSeoDecision::decide(
            listingId: $listing->listingId,
            canonical: $canonical,
            canonicalHistory: $canonicalHistory,
            headline: $title,
            description: $description,
            indexability: $indexable ? SeoIndexability::Indexable : SeoIndexability::NotIndexable,
            robots: $robots,
            htmlRobotsDirective: HtmlRobotsDirective::fromPolicy($robots),
            pageTreatment: $this->pageTreatment($listing, $indexable),
            breadcrumb: $breadcrumb,
            structuredData: $structuredData,
            publicJsonLd: $publicJsonLd,
            publicMedia: $media?->url,
            expiredTreatment: $this->expiredTreatment($listing),
            publishedAt: $listing->publishedAt,
            expiresAt: $listing->expiresAt,
            decidedAt: $at,
        );
    }

    /** @return array{?SeoTitle, ?MetaDescription} */
    private function publicContent(ListingSeoSource $listing): array
    {
        try {
            return [SeoTitle::fromString($listing->headline.' | APPART.SN'), MetaDescription::fromString($listing->description)];
        } catch (InvalidSeoValue) {
            return [null, null];
        }
    }

    private function expiredTreatment(ListingSeoSource $listing): ExpiredListingTreatment
    {
        if ($listing->state !== ListingSeoState::Expired) {
            return ExpiredListingTreatment::NotApplicable;
        }

        return $listing->expiredTreatment === ExpiredListingTreatment::RetainNoIndex
            ? ExpiredListingTreatment::RetainNoIndex
            : ExpiredListingTreatment::Remove;
    }

    private function pageTreatment(ListingSeoSource $listing, bool $indexable): SeoPageTreatment
    {
        if ($indexable) {
            return SeoPageTreatment::Retain;
        }
        if ($listing->state === ListingSeoState::Expired) {
            return $listing->expiredTreatment === ExpiredListingTreatment::RetainNoIndex
                ? SeoPageTreatment::Retain
                : SeoPageTreatment::Remove;
        }

        return $listing->nonIndexablePageTreatment;
    }

    /**
     * @param  list<CanonicalHistoryEntry>  $history
     * @return list<CanonicalHistoryEntry>
     */
    private function canonicalHistory(CanonicalUrl $canonical, array $history, DateTimeImmutable $at): array
    {
        if ($history === []) {
            return [new CanonicalHistoryEntry($canonical, CanonicalDisposition::Current, $at)];
        }

        $current = null;
        foreach ($history as $entry) {
            if ($entry->disposition === CanonicalDisposition::Current) {
                if ($current !== null) {
                    throw new SeoViolation('Canonical history has multiple current entries.');
                }
                $current = $entry;
            }
        }
        if ($current === null || $at < $current->effectiveAt) {
            throw new SeoViolation('Canonical history has no valid current entry.');
        }
        if ($current->canonical->value === $canonical->value) {
            return $history;
        }

        return $this->history->replace($history, $current->canonical, $canonical, $at);
    }

    private function assertSameListing(ListingSeoSource $listing, SearchSeoSource $search, PropertySeoSource $property, ?PublicGeographySeoSource $geography, ?PublicMediaSeoSource $media): void
    {
        $expected = $listing->listingId->value;
        if ($search->listingId->value !== $expected || $property->listingId->value !== $expected || ($geography !== null && $geography->listingId->value !== $expected) || ($media !== null && $media->listingId->value !== $expected)) {
            throw new InconsistentSeoSources;
        }
    }
}
