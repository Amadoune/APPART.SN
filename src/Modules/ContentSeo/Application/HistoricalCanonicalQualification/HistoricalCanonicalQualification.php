<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification;

use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;

final readonly class HistoricalCanonicalQualification
{
    private function __construct(
        public CanonicalUrl $canonical,
        public HistoricalCanonicalQualificationStatus $status,
        public ?HistoricalCanonical $historicalCanonical,
        public ?HistoricalCanonicalQualificationDiagnostic $diagnostic,
    ) {}

    public static function current(CanonicalUrl $canonical): self
    {
        return new self($canonical, HistoricalCanonicalQualificationStatus::Current, null, null);
    }

    public static function historical(CanonicalUrl $canonical): self
    {
        return new self(
            $canonical,
            HistoricalCanonicalQualificationStatus::Historical,
            HistoricalCanonical::declared($canonical),
            null,
        );
    }

    public static function unknown(CanonicalUrl $canonical): self
    {
        return self::failed($canonical, HistoricalCanonicalQualificationStatus::Unknown, HistoricalCanonicalQualificationDiagnostic::canonicalUnknown());
    }

    public static function ambiguous(CanonicalUrl $canonical): self
    {
        return self::failed($canonical, HistoricalCanonicalQualificationStatus::Ambiguous, HistoricalCanonicalQualificationDiagnostic::conflictingQualifications());
    }

    public static function corrupted(CanonicalUrl $canonical): self
    {
        return self::failed($canonical, HistoricalCanonicalQualificationStatus::Corrupted, HistoricalCanonicalQualificationDiagnostic::qualificationCorrupted());
    }

    private static function failed(
        CanonicalUrl $canonical,
        HistoricalCanonicalQualificationStatus $status,
        HistoricalCanonicalQualificationDiagnostic $diagnostic,
    ): self {
        return new self($canonical, $status, null, $diagnostic);
    }
}
