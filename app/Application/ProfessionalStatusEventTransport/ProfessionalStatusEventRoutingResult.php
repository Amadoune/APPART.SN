<?php

namespace App\Application\ProfessionalStatusEventTransport;

use InvalidArgumentException;

final readonly class ProfessionalStatusEventRoutingResult
{
    private function __construct(public ProfessionalStatusEventRoutingStatus $status, public ?ProfessionalStatusEventRoutingDiagnostic $diagnostic) {}

    public static function routed(): self
    {
        return new self(ProfessionalStatusEventRoutingStatus::Routed, null);
    }

    public static function deferred(): self
    {
        return new self(ProfessionalStatusEventRoutingStatus::Deferred, ProfessionalStatusEventRoutingDiagnostic::RouteUnavailable);
    }

    public static function retryableFailure(): self
    {
        return new self(ProfessionalStatusEventRoutingStatus::RetryableFailure, ProfessionalStatusEventRoutingDiagnostic::TransferFailed);
    }

    public static function rejected(ProfessionalStatusEventRoutingDiagnostic $diagnostic): self
    {
        if (! in_array($diagnostic, [ProfessionalStatusEventRoutingDiagnostic::UnsupportedEvent, ProfessionalStatusEventRoutingDiagnostic::CorruptedEvent], true)) {
            throw new InvalidArgumentException('Rejected routing requires a permanent diagnostic.');
        }

        return new self(ProfessionalStatusEventRoutingStatus::Rejected, $diagnostic);
    }

    public function acknowledgesDelivery(): bool
    {
        return $this->status === ProfessionalStatusEventRoutingStatus::Routed;
    }
}
