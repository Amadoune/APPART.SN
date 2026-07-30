<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification;

enum HistoricalCanonicalQualificationDiagnosticCode: string
{
    case CanonicalUnknown = 'canonical_unknown';
    case ConflictingQualifications = 'conflicting_qualifications';
    case QualificationCorrupted = 'qualification_corrupted';
}
