<?php

namespace Appart\Modules\ContentSeo\Domain\Policy;

use Appart\Modules\ContentSeo\Domain\Exception\InconsistentSeoSources;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SeoMaterial;
use Appart\Modules\ContentSeo\Domain\Model\StructuredData;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\MetaDescription;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevisions;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoTitle;
use Appart\Modules\ContentSeo\Domain\ValueObject\SitemapPriority;
use Appart\Modules\ContentSeo\Domain\ValueObject\StructuredDataType;

final readonly class SeoGenerationPolicy
{
    public function __construct(private CanonicalPolicy $canonicals) {}

    public function generate(ListingSeoSource $listing, SearchSeoSource $search, PropertySeoSource $property): SeoMaterial
    {
        if ($listing->listingId->value !== $search->listingId->value || $listing->listingId->value !== $property->listingId->value) {
            throw new InconsistentSeoSources;
        }
        $eligible = $listing->state === ListingSeoState::Published && $search->state === SearchSeoState::Public && $property->state === PropertySeoState::Available;
        $state = $eligible ? SeoProjectionState::Active : SeoProjectionState::Removed;
        $canonical = $this->canonicals->fromPath($listing->canonicalPath);
        $title = SeoTitle::fromString($listing->headline.' | APPART.SN');
        $description = MetaDescription::fromString($listing->description);
        $structured = new StructuredData(StructuredDataType::RealEstateListing, [
            'name' => $title->value,
            'url' => $canonical->value,
            'category' => $property->propertyType,
            'addressLocality' => $property->city,
        ]);

        return SeoMaterial::derived(
            $state,
            $title,
            $description,
            $canonical,
            $eligible ? RobotsPolicy::IndexFollow : RobotsPolicy::NoIndexFollow,
            $structured,
            $eligible,
            SitemapPriority::fromPercent($eligible ? 70 : 0),
            new SeoSourceRevisions($listing->revision, $search->revision, $property->revision),
        );
    }
}
