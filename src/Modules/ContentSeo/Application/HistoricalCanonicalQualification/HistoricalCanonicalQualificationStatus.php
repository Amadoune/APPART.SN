<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification;

enum HistoricalCanonicalQualificationStatus: string
{
    case Current = 'current';
    case Historical = 'historical';
    case Unknown = 'unknown';
    case Ambiguous = 'ambiguous';
    case Corrupted = 'corrupted';
}
