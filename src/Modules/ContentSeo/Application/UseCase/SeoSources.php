<?php

namespace Appart\Modules\ContentSeo\Application\UseCase;

use Appart\Modules\ContentSeo\Application\Contract\ListingCatalog;
use Appart\Modules\ContentSeo\Application\Contract\PropertyCatalog;
use Appart\Modules\ContentSeo\Application\Contract\SearchCatalog;
use Appart\Modules\ContentSeo\Domain\Exception\SeoSourceUnavailable;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

final readonly class SeoSources
{
    public function __construct(private ListingCatalog $listings, private SearchCatalog $search, private PropertyCatalog $properties) {}

    /** @return array{ListingSeoSource, SearchSeoSource, PropertySeoSource} */
    public function load(ListingId $id): array
    {
        return [
            $this->listings->seoSourceFor($id) ?? throw new SeoSourceUnavailable('Listing SEO source unavailable.'),
            $this->search->seoSourceFor($id) ?? throw new SeoSourceUnavailable('Search SEO source unavailable.'),
            $this->properties->seoSourceFor($id) ?? throw new SeoSourceUnavailable('Property SEO source unavailable.'),
        ];
    }
}
