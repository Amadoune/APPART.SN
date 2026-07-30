<?php

namespace Appart\Modules\MonetizationPayments\Domain\Model;

use Appart\Modules\MonetizationPayments\Domain\Event\PaymentEvent;
use Appart\Modules\MonetizationPayments\Domain\Exception\InvalidPaymentValue;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitType;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Money;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\ProductId;

final class Product
{
    private int $version = 1;

    /** @var list<ProductHistoryEntry> */
    private array $history = [];

    private function __construct(private readonly ProductId $id, private readonly string $name, private readonly Money $price, private readonly BenefitType $benefitType, private readonly int $benefitDays) {}

    public static function define(ProductId $id, string $name, Money $price, BenefitType $benefitType, int $benefitDays): self
    {
        $name = trim($name);
        if (mb_strlen($name) < 3 || mb_strlen($name) > 120 || $benefitDays < 1 || $benefitDays > 365) {
            throw InvalidPaymentValue::field('product');
        }

        $self = new self($id, $name, $price, $benefitType, $benefitDays);
        $self->history[] = new ProductHistoryEntry('defined', 1);

        return $self;
    }

    /** @param list<ProductHistoryEntry> $history */
    public static function reconstitute(ProductId $id, string $name, Money $price, BenefitType $benefitType, int $benefitDays, int $version, array $history): self
    {
        $name = trim($name);
        $last = $history === [] ? null : $history[array_key_last($history)];
        if ($version !== 1 || count($history) !== 1 || $last === null || $last->version !== 1 || $last->action !== 'defined' || mb_strlen($name) < 3 || mb_strlen($name) > 120 || $benefitDays < 1 || $benefitDays > 365) {
            throw InvalidPaymentValue::field('product_history');
        }
        $self = new self($id, $name, $price, $benefitType, $benefitDays);
        $self->version = $version;
        $self->history = $history;

        return $self;
    }

    public function id(): ProductId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function benefitType(): BenefitType
    {
        return $this->benefitType;
    }

    public function benefitDays(): int
    {
        return $this->benefitDays;
    }

    public function version(): int
    {
        return $this->version;
    }

    /** @return list<ProductHistoryEntry> */
    public function history(): array
    {
        return $this->history;
    }

    /**
     * Product currently produces no domain events; reconstruction always remains event-free.
     *
     * @return list<PaymentEvent>
     */
    public function releaseEvents(): array
    {
        return [];
    }
}
