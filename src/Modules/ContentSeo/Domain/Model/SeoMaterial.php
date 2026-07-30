<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\MetaDescription;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevisions;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoTitle;
use Appart\Modules\ContentSeo\Domain\ValueObject\SitemapPriority;

final readonly class SeoMaterial
{
    private function __construct(
        public SeoProjectionState $state,
        public SeoTitle $title,
        public MetaDescription $description,
        public CanonicalUrl $canonical,
        public RobotsPolicy $robots,
        public StructuredData $structuredData,
        public bool $inSitemap,
        public SitemapPriority $sitemapPriority,
        public SeoSourceRevisions $revisions,
    ) {}

    public static function derived(SeoProjectionState $state, SeoTitle $title, MetaDescription $description, CanonicalUrl $canonical, RobotsPolicy $robots, StructuredData $structuredData, bool $inSitemap, SitemapPriority $priority, SeoSourceRevisions $revisions): self
    {
        $active = $state === SeoProjectionState::Active;
        if (($active && ($robots !== RobotsPolicy::IndexFollow || ! $inSitemap || $priority->percent === 0))
            || (! $active && ($robots !== RobotsPolicy::NoIndexFollow || $inSitemap || $priority->percent !== 0))
            || $structuredData->facts['url'] !== $canonical->value
            || $structuredData->facts['name'] !== $title->value) {
            throw new SeoViolation('Inconsistent SEO material.');
        }

        return new self($state, $title, $description, $canonical, $robots, $structuredData, $inSitemap, $priority, $revisions);
    }

    public function failSafe(): self
    {
        return new self($this->state === SeoProjectionState::Removed ? $this->state : SeoProjectionState::Removed, $this->title, $this->description, $this->canonical, RobotsPolicy::NoIndexFollow, $this->structuredData, false, SitemapPriority::fromPercent(0), $this->revisions);
    }
}
