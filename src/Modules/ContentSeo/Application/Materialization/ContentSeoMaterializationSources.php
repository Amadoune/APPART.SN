<?php

namespace Appart\Modules\ContentSeo\Application\Materialization;

use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use DateTimeImmutable;

final readonly class ContentSeoMaterializationSources
{
    public function __construct(
        public ListingSeoSource $listing,
        public SearchSeoSource $search,
        public PropertySeoSource $property,
        public DateTimeImmutable $decisionAt,
    ) {}
}
