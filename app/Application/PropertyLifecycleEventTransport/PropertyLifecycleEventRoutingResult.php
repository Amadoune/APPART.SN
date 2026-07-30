<?php

namespace App\Application\PropertyLifecycleEventTransport;

final readonly class PropertyLifecycleEventRoutingResult
{
    private function __construct(
        public PropertyLifecycleEventRoutingStatus $status,
        public ?PropertyLifecycleEventRoutingDiagnosticCode $diagnostic,
    ) {}

    public static function routed(): self
    {
        return new self(PropertyLifecycleEventRoutingStatus::Routed, null);
    }

    public static function deferred(PropertyLifecycleEventRoutingDiagnosticCode $diagnostic): self
    {
        return new self(PropertyLifecycleEventRoutingStatus::Deferred, $diagnostic);
    }

    public static function retryableFailure(PropertyLifecycleEventRoutingDiagnosticCode $diagnostic): self
    {
        return new self(PropertyLifecycleEventRoutingStatus::RetryableFailure, $diagnostic);
    }

    public static function rejected(PropertyLifecycleEventRoutingDiagnosticCode $diagnostic): self
    {
        return new self(PropertyLifecycleEventRoutingStatus::Rejected, $diagnostic);
    }

    public function acknowledgesDelivery(): bool
    {
        return $this->status === PropertyLifecycleEventRoutingStatus::Routed;
    }
}
