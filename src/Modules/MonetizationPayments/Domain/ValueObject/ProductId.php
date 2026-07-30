<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

use Appart\Modules\MonetizationPayments\Domain\Exception\InvalidPaymentValue;

final readonly class ProductId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        return new self(self::uuid($value));
    }

    private static function uuid(string $value): string
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) !== 1) {
            throw InvalidPaymentValue::field('product_id');
        }

        return mb_strtolower($value);
    }
}
