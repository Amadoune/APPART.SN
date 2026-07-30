<?php

namespace App\Application\ListingPublicationEventTransport;

final readonly class ListingPublicationEventRoutingResult
{
    private function __construct(
        public ListingPublicationEventRoutingStatus $status,
        public ?ListingPublicationEventRoutingDiagnosticCode $diagnostic,
    ) {}

    public static function routed(): self
    {
        return new self(ListingPublicationEventRoutingStatus::Routed, null);
    }

    public static function deferred(ListingPublicationEventRoutingDiagnosticCode $diagnostic): self
    {
        return new self(ListingPublicationEventRoutingStatus::Deferred, $diagnostic);
    }

    public static function retryableFailure(ListingPublicationEventRoutingDiagnosticCode $diagnostic): self
    {
        return new self(ListingPublicationEventRoutingStatus::RetryableFailure, $diagnostic);
    }

    public static function rejected(ListingPublicationEventRoutingDiagnosticCode $diagnostic): self
    {
        return new self(ListingPublicationEventRoutingStatus::Rejected, $diagnostic);
    }

    public function acknowledgesDelivery(): bool
    {
        return $this->status === ListingPublicationEventRoutingStatus::Routed;
    }
}
