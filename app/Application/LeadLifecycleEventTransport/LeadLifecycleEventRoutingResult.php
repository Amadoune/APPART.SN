<?php

namespace App\Application\LeadLifecycleEventTransport;

final readonly class LeadLifecycleEventRoutingResult
{
    private function __construct(
        public LeadLifecycleEventRoutingStatus $status,
        public ?LeadLifecycleEventRoutingDiagnostic $diagnostic,
    ) {}

    public static function routed(): self
    {
        return new self(LeadLifecycleEventRoutingStatus::Routed, null);
    }

    public static function deferred(): self
    {
        return new self(LeadLifecycleEventRoutingStatus::Deferred, LeadLifecycleEventRoutingDiagnostic::RouteUnavailable);
    }

    public static function retryableFailure(): self
    {
        return new self(LeadLifecycleEventRoutingStatus::RetryableFailure, LeadLifecycleEventRoutingDiagnostic::TransferFailed);
    }

    public static function rejected(LeadLifecycleEventRoutingDiagnostic $diagnostic): self
    {
        if (! in_array($diagnostic, [LeadLifecycleEventRoutingDiagnostic::UnsupportedEvent, LeadLifecycleEventRoutingDiagnostic::CorruptedEvent], true)) {
            throw new \InvalidArgumentException('Rejected routing requires a permanent diagnostic.');
        }

        return new self(LeadLifecycleEventRoutingStatus::Rejected, $diagnostic);
    }

    public function acknowledgesDelivery(): bool
    {
        return $this->status === LeadLifecycleEventRoutingStatus::Routed;
    }
}
