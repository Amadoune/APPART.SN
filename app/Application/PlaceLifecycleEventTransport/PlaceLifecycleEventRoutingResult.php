<?php

namespace App\Application\PlaceLifecycleEventTransport;

use InvalidArgumentException;

final readonly class PlaceLifecycleEventRoutingResult
{
    private function __construct(
        public PlaceLifecycleEventRoutingStatus $status,
        public ?PlaceLifecycleEventRoutingDiagnostic $diagnostic,
    ) {}

    public static function routed(): self
    {
        return new self(PlaceLifecycleEventRoutingStatus::Routed, null);
    }

    public static function deferred(): self
    {
        return new self(
            PlaceLifecycleEventRoutingStatus::Deferred,
            PlaceLifecycleEventRoutingDiagnostic::RouteUnavailable,
        );
    }

    public static function retryableFailure(): self
    {
        return new self(
            PlaceLifecycleEventRoutingStatus::RetryableFailure,
            PlaceLifecycleEventRoutingDiagnostic::TransferFailed,
        );
    }

    public static function rejected(PlaceLifecycleEventRoutingDiagnostic $diagnostic): self
    {
        if (! in_array($diagnostic, [
            PlaceLifecycleEventRoutingDiagnostic::UnsupportedEvent,
            PlaceLifecycleEventRoutingDiagnostic::CorruptedEvent,
        ], true)) {
            throw new InvalidArgumentException('Rejected routing requires a permanent diagnostic.');
        }

        return new self(PlaceLifecycleEventRoutingStatus::Rejected, $diagnostic);
    }

    public function acknowledgesDelivery(): bool
    {
        return $this->status === PlaceLifecycleEventRoutingStatus::Routed;
    }
}
