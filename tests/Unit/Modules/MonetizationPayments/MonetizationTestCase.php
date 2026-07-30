<?php

namespace Tests\Unit\Modules\MonetizationPayments;

use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\Model\OrderLine;
use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\Model\Product;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitType;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Currency;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Money;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentMethod;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\ProductId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class MonetizationTestCase extends TestCase
{
    protected function product(int $suffix = 1, Currency $currency = Currency::XOF): Product
    {
        return Product::define($this->productId($suffix), 'Visibilité Premium', new Money(5000, $currency), BenefitType::FeaturedPlacement, 30);
    }

    protected function order(): Order
    {
        $p = $this->product();

        return Order::create($this->orderId(), [new OrderLine($p->id(), $p->price(), $p->benefitType(), $p->benefitDays())], $this->at(0));
    }

    protected function capturedPayment(int $suffix = 1): Payment
    {
        $payment = Payment::authorize($this->paymentId($suffix), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::MobileMoney, TransactionReference::fromString(sprintf('TX-%06d', $suffix)), $this->at(0));
        $payment->capture($this->at(1));

        return $payment;
    }

    protected function productId(int $n = 1): ProductId
    {
        return ProductId::fromString(sprintf('10000000-0000-4000-8000-%012d', $n));
    }

    protected function orderId(int $n = 1): OrderId
    {
        return OrderId::fromString(sprintf('20000000-0000-4000-8000-%012d', $n));
    }

    protected function paymentId(int $n = 1): PaymentId
    {
        return PaymentId::fromString(sprintf('30000000-0000-4000-8000-%012d', $n));
    }

    protected function at(int $day): OccurredAt
    {
        return new OccurredAt((new DateTimeImmutable('2026-07-17T10:00:00+00:00'))->modify("+{$day} days"));
    }
}
