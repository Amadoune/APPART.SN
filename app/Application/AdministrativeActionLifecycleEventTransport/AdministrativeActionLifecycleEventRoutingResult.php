<?php

namespace App\Application\AdministrativeActionLifecycleEventTransport;

use InvalidArgumentException;

final readonly class AdministrativeActionLifecycleEventRoutingResult
{
    private function __construct(
        public AdministrativeActionLifecycleEventRoutingStatus $status,
        public ?AdministrativeActionLifecycleEventRoutingDiagnostic $diagnostic,
    ) {}

    public static function routed(): self
    {
        return new self(AdministrativeActionLifecycleEventRoutingStatus::Routed, null);
    }

    public static function deferred(): self
    {
        return new self(
            AdministrativeActionLifecycleEventRoutingStatus::Deferred,
            AdministrativeActionLifecycleEventRoutingDiagnostic::RouteUnavailable,
        );
    }

    public static function retryableFailure(): self
    {
        return new self(
            AdministrativeActionLifecycleEventRoutingStatus::RetryableFailure,
            AdministrativeActionLifecycleEventRoutingDiagnostic::TransferFailed,
        );
    }

    public static function rejected(AdministrativeActionLifecycleEventRoutingDiagnostic $diagnostic): self
    {
        if (! in_array($diagnostic, [
            AdministrativeActionLifecycleEventRoutingDiagnostic::UnsupportedEvent,
            AdministrativeActionLifecycleEventRoutingDiagnostic::CorruptedEvent,
        ], true)) {
            throw new InvalidArgumentException('Rejected routing requires a permanent diagnostic.');
        }

        return new self(AdministrativeActionLifecycleEventRoutingStatus::Rejected, $diagnostic);
    }

    public function acknowledgesDelivery(): bool
    {
        return $this->status === AdministrativeActionLifecycleEventRoutingStatus::Routed;
    }
}
