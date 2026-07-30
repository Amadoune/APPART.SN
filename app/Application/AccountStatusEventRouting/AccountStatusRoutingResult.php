<?php

namespace App\Application\AccountStatusEventRouting;

use App\Application\AccountStatusEventTransport\AccountStatusDeliveryMessage;

final readonly class AccountStatusRoutingResult
{
    private function __construct(
        public AccountStatusRoutingStatus $status,
        public ?AccountStatusRoutingDestination $destination,
        public ?AccountStatusRoutingDiagnostic $diagnostic,
        public AccountStatusDeliveryMessage $message,
    ) {}

    public static function routed(
        AccountStatusDeliveryMessage $message,
        AccountStatusRoutingDestination $destination,
    ): self {
        return new self(AccountStatusRoutingStatus::Routed, $destination, null, $message);
    }

    public static function rejected(
        AccountStatusDeliveryMessage $message,
        AccountStatusRoutingDiagnostic $diagnostic,
    ): self {
        return new self(AccountStatusRoutingStatus::Rejected, null, $diagnostic, $message);
    }
}
