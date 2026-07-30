<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalRedirect;

final readonly class HistoricalRedirectDiagnostic
{
    private function __construct(public HistoricalRedirectDiagnosticCode $code) {}

    public static function unknownHistoricalCanonical(): self
    {
        return new self(HistoricalRedirectDiagnosticCode::UnknownHistoricalCanonical);
    }

    public static function publicDestinationMissing(): self
    {
        return new self(HistoricalRedirectDiagnosticCode::PublicDestinationMissing);
    }

    public static function destinationEqualsSource(): self
    {
        return new self(HistoricalRedirectDiagnosticCode::DestinationEqualsSource);
    }

    public static function destinationIsHistorical(): self
    {
        return new self(HistoricalRedirectDiagnosticCode::DestinationIsHistorical);
    }

    public static function multiplePublicDestinations(): self
    {
        return new self(HistoricalRedirectDiagnosticCode::MultiplePublicDestinations);
    }

    public static function storedDecisionCorrupted(): self
    {
        return new self(HistoricalRedirectDiagnosticCode::StoredDecisionCorrupted);
    }
}
