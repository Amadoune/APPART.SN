<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalRedirect;

use Appart\Modules\ContentSeo\Application\HistoricalRedirect\Exception\InvalidHistoricalRedirectResolution;

final readonly class HistoricalRedirectResolution
{
    private function __construct(
        public HistoricalCanonical $source,
        public HistoricalRedirectStatus $status,
        public ?HistoricalRedirectTarget $target,
        public ?HistoricalRedirectDiagnostic $diagnostic,
    ) {}

    public static function resolved(HistoricalCanonical $source, HistoricalRedirectTarget $target): self
    {
        if ($source->canonical->value === $target->canonical->value) {
            throw InvalidHistoricalRedirectResolution::destinationEqualsSource();
        }

        return new self($source, HistoricalRedirectStatus::Resolved, $target, null);
    }

    public static function notFound(HistoricalCanonical $source): self
    {
        return self::failed($source, HistoricalRedirectStatus::NotFound, HistoricalRedirectDiagnostic::unknownHistoricalCanonical());
    }

    public static function destinationMissing(HistoricalCanonical $source): self
    {
        return self::failed($source, HistoricalRedirectStatus::DestinationMissing, HistoricalRedirectDiagnostic::publicDestinationMissing());
    }

    public static function loopDetected(HistoricalCanonical $source): self
    {
        return self::failed($source, HistoricalRedirectStatus::LoopDetected, HistoricalRedirectDiagnostic::destinationEqualsSource());
    }

    public static function chainDetected(HistoricalCanonical $source): self
    {
        return self::failed($source, HistoricalRedirectStatus::ChainDetected, HistoricalRedirectDiagnostic::destinationIsHistorical());
    }

    public static function ambiguous(HistoricalCanonical $source): self
    {
        return self::failed($source, HistoricalRedirectStatus::Ambiguous, HistoricalRedirectDiagnostic::multiplePublicDestinations());
    }

    public static function corrupted(HistoricalCanonical $source): self
    {
        return self::failed($source, HistoricalRedirectStatus::Corrupted, HistoricalRedirectDiagnostic::storedDecisionCorrupted());
    }

    private static function failed(
        HistoricalCanonical $source,
        HistoricalRedirectStatus $status,
        HistoricalRedirectDiagnostic $diagnostic,
    ): self {
        return new self($source, $status, null, $diagnostic);
    }
}
