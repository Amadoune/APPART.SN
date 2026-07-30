<?php

namespace Appart\Modules\MonetizationPayments\Domain\Model;

use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitType;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Money;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\ProductId;

final readonly class OrderLine
{
    /** Business decision: one line represents exactly one product; quantities are not supported. */
    public const int QUANTITY = 1;

    public function __construct(public ProductId $productId, public Money $unitPrice, public BenefitType $benefitType, public int $benefitDays)
    {
        if ($benefitDays < 1) {
            throw new \InvalidArgumentException('Benefit duration must be positive.');
        }
    }

    public function quantity(): int
    {
        return self::QUANTITY;
    }
}
