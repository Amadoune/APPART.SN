<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

use Appart\Modules\MonetizationPayments\Domain\Exception\InvalidPaymentValue;
use Appart\Modules\MonetizationPayments\Domain\Exception\PaymentViolation;

final readonly class Money
{
    public function __construct(public int $minorAmount, public Currency $currency)
    {
        if ($minorAmount <= 0) {
            throw InvalidPaymentValue::field('money');
        }
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new PaymentViolation('A single currency is required.');
        }

        return new self($this->minorAmount + $other->minorAmount, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->minorAmount === $other->minorAmount && $this->currency === $other->currency;
    }
}
