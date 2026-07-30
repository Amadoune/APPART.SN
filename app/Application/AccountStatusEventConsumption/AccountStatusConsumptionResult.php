<?php

namespace App\Application\AccountStatusEventConsumption;

final readonly class AccountStatusConsumptionResult
{
    private function __construct(
        public AccountStatusConsumptionStatus $status,
        public ?AccountStatusConsumptionDiagnostic $diagnostic,
        public ?AccountStatusConsumedFact $fact,
    ) {}

    public static function consumed(AccountStatusConsumedFact $fact): self
    {
        return new self(AccountStatusConsumptionStatus::Consumed, null, $fact);
    }

    public static function rejected(AccountStatusConsumptionDiagnostic $diagnostic): self
    {
        return new self(AccountStatusConsumptionStatus::Rejected, $diagnostic, null);
    }
}
