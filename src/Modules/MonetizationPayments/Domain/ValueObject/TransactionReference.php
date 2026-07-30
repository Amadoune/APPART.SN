<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

use Appart\Modules\MonetizationPayments\Domain\Exception\InvalidPaymentValue;

final readonly class TransactionReference
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = mb_strtoupper(trim($value));
        if (preg_match('/^[A-Z0-9][A-Z0-9._-]{5,79}$/', $value) !== 1) {
            throw InvalidPaymentValue::field('transaction_reference');
        }

        return new self($value);
    }
}
