<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification;

final readonly class HistoricalCanonicalQualificationDiagnostic
{
    private function __construct(public HistoricalCanonicalQualificationDiagnosticCode $code) {}

    public static function canonicalUnknown(): self
    {
        return new self(HistoricalCanonicalQualificationDiagnosticCode::CanonicalUnknown);
    }

    public static function conflictingQualifications(): self
    {
        return new self(HistoricalCanonicalQualificationDiagnosticCode::ConflictingQualifications);
    }

    public static function qualificationCorrupted(): self
    {
        return new self(HistoricalCanonicalQualificationDiagnosticCode::QualificationCorrupted);
    }
}
