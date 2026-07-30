<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualification;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;

interface HistoricalCanonicalQualifier
{
    public function qualify(CanonicalUrl $canonical): HistoricalCanonicalQualification;
}
