<?php

namespace Appart\Modules\MonetizationPayments\Domain\Exception;

final class InvalidPaymentValue extends PaymentDomainException
{
    public static function field(string $field): self
    {
        return new self("Invalid {$field}.");
    }
}
