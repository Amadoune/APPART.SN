<?php

namespace App\Application\MediaItemLifecycleEventTransport;

use InvalidArgumentException;

final readonly class MediaItemLifecycleEventRoutingResult
{
    private function __construct(public MediaItemLifecycleEventRoutingStatus $status, public ?MediaItemLifecycleEventRoutingDiagnostic $diagnostic) {}

    public static function routed(): self
    {
        return new self(MediaItemLifecycleEventRoutingStatus::Routed, null);
    }

    public static function deferred(): self
    {
        return new self(MediaItemLifecycleEventRoutingStatus::Deferred, MediaItemLifecycleEventRoutingDiagnostic::RouteUnavailable);
    }

    public static function retryableFailure(): self
    {
        return new self(MediaItemLifecycleEventRoutingStatus::RetryableFailure, MediaItemLifecycleEventRoutingDiagnostic::TransferFailed);
    }

    public static function rejected(MediaItemLifecycleEventRoutingDiagnostic $diagnostic): self
    {
        if (! in_array($diagnostic, [MediaItemLifecycleEventRoutingDiagnostic::UnsupportedEvent, MediaItemLifecycleEventRoutingDiagnostic::CorruptedEvent], true)) {
            throw new InvalidArgumentException('Rejected routing requires a permanent diagnostic.');
        }

        return new self(MediaItemLifecycleEventRoutingStatus::Rejected, $diagnostic);
    }

    public function acknowledgesDelivery(): bool
    {
        return $this->status === MediaItemLifecycleEventRoutingStatus::Routed;
    }
}
