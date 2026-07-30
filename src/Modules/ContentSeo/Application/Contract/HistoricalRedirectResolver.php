<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectResolution;

interface HistoricalRedirectResolver
{
    public function resolve(HistoricalCanonical $canonical): HistoricalRedirectResolution;
}
