<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ExpiredListingTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\HtmlRobotsDirective;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\MetaDescription;
use Appart\Modules\ContentSeo\Domain\ValueObject\PublicMediaUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoIndexability;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoPageTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoTitle;
use DateTimeImmutable;

final readonly class ListingSeoDecision
{
    /**
     * @param  list<CanonicalHistoryEntry>  $canonicalHistory
     * @param  list<BreadcrumbItem>  $breadcrumb
     * @param  list<PublicGeographyBreadcrumbItemV2>  $geographyBreadcrumbV2
     */
    private function __construct(
        public ListingId $listingId,
        public CanonicalUrl $canonical,
        public array $canonicalHistory,
        public ?SeoTitle $headline,
        public ?MetaDescription $description,
        public SeoIndexability $indexability,
        public RobotsPolicy $robots,
        public HtmlRobotsDirective $htmlRobotsDirective,
        public SeoPageTreatment $pageTreatment,
        public array $breadcrumb,
        public ?StructuredData $structuredData,
        public ?PublicJsonLd $publicJsonLd,
        public ?PublicMediaUrl $publicMedia,
        public ExpiredListingTreatment $expiredTreatment,
        public ?DateTimeImmutable $publishedAt,
        public ?DateTimeImmutable $expiresAt,
        public DateTimeImmutable $decidedAt,
        public array $geographyBreadcrumbV2 = [],
    ) {}

    /**
     * @param  list<CanonicalHistoryEntry>  $canonicalHistory
     * @param  list<BreadcrumbItem>  $breadcrumb
     * @param  list<PublicGeographyBreadcrumbItemV2>  $geographyBreadcrumbV2
     */
    public static function decide(
        ListingId $listingId,
        CanonicalUrl $canonical,
        array $canonicalHistory,
        ?SeoTitle $headline,
        ?MetaDescription $description,
        SeoIndexability $indexability,
        RobotsPolicy $robots,
        HtmlRobotsDirective $htmlRobotsDirective,
        SeoPageTreatment $pageTreatment,
        array $breadcrumb,
        ?StructuredData $structuredData,
        ?PublicJsonLd $publicJsonLd,
        ?PublicMediaUrl $publicMedia,
        ExpiredListingTreatment $expiredTreatment,
        ?DateTimeImmutable $publishedAt,
        ?DateTimeImmutable $expiresAt,
        DateTimeImmutable $decidedAt,
        array $geographyBreadcrumbV2 = [],
    ): self {
        $indexable = $indexability === SeoIndexability::Indexable;
        if (($indexable && ($robots !== RobotsPolicy::IndexFollow || $htmlRobotsDirective->value !== 'index, follow' || $pageTreatment !== SeoPageTreatment::Retain || $headline === null || $description === null || $breadcrumb === [] || $structuredData === null || $publicJsonLd === null || $publicMedia === null || $publishedAt === null || $expiresAt === null || $expiredTreatment !== ExpiredListingTreatment::NotApplicable))
            || (! $indexable && $robots !== RobotsPolicy::NoIndexFollow)
            || (! $indexable && ($htmlRobotsDirective->value !== 'noindex, follow' || $publicJsonLd !== null))
            || ($publicJsonLd !== null && ($publicJsonLd->document['url'] !== $canonical->value || $publicJsonLd->document['name'] !== $headline->value || (($publicJsonLd->document['image'] ?? null) !== $publicMedia->value)))
            || ($expiredTreatment === ExpiredListingTreatment::Remove && $pageTreatment !== SeoPageTreatment::Remove)
            || ($expiredTreatment === ExpiredListingTreatment::RetainNoIndex && ($pageTreatment !== SeoPageTreatment::Retain || $indexable))
            || $canonicalHistory === []
            || ! self::hasCurrentCanonical($canonicalHistory, $canonical)) {
            throw new SeoViolation('Inconsistent listing SEO decision.');
        }

        return new self($listingId, $canonical, $canonicalHistory, $headline, $description, $indexability, $robots, $htmlRobotsDirective, $pageTreatment, $breadcrumb, $structuredData, $publicJsonLd, $publicMedia, $expiredTreatment, $publishedAt, $expiresAt, $decidedAt, $geographyBreadcrumbV2);
    }

    /** @param list<CanonicalHistoryEntry> $history */
    private static function hasCurrentCanonical(array $history, CanonicalUrl $canonical): bool
    {
        foreach ($history as $entry) {
            if ($entry->disposition === CanonicalDisposition::Current && $entry->canonical->value === $canonical->value) {
                return true;
            }
        }

        return false;
    }
}
