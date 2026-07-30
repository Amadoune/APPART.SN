<?php

namespace Tests\Unit\Modules\MonetizationPayments;

use Appart\Modules\MonetizationPayments\Domain\Event\BenefitExpired;
use Appart\Modules\MonetizationPayments\Domain\Event\BenefitGranted;
use Appart\Modules\MonetizationPayments\Domain\Event\OrderCreated;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentAuthorized;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentCaptured;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentFailed;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentRefunded;
use Appart\Modules\MonetizationPayments\Domain\Exception\InvalidPaymentValue;
use Appart\Modules\MonetizationPayments\Domain\Exception\PaymentViolation;
use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\Model\OrderLine;
use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Currency;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Money;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentMethod;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;

final class MonetizationDomainTest extends MonetizationTestCase
{
    public function test_product_has_stable_commercial_definition(): void
    {
        $product = $this->product();
        self::assertSame(5000, $product->price()->minorAmount);
        self::assertSame(30, $product->benefitDays());
    }

    public function test_money_must_be_strictly_positive(): void
    {
        $this->expectException(InvalidPaymentValue::class);
        new Money(0, Currency::XOF);
    }

    public function test_order_creation_freezes_lines_and_emits_event(): void
    {
        $order = $this->order();
        self::assertSame(OrderStatus::PendingPayment, $order->status());
        self::assertCount(1, $order->lines());
        self::assertInstanceOf(OrderCreated::class, $order->releaseEvents()[0]);
    }

    public function test_order_rejects_mixed_currencies_without_partial_construction(): void
    {
        $first = $this->product(1, Currency::XOF);
        $second = $this->product(2, Currency::EUR);
        $this->expectException(PaymentViolation::class);
        Order::create($this->orderId(), [new OrderLine($first->id(), $first->price(), $first->benefitType(), 30), new OrderLine($second->id(), $second->price(), $second->benefitType(), 30)], $this->at(0));
    }

    public function test_payment_authorization_and_capture_are_coherent(): void
    {
        $payment = Payment::authorize($this->paymentId(), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::MobileMoney, TransactionReference::fromString('TX-000001'), $this->at(0));
        self::assertInstanceOf(PaymentAuthorized::class, $payment->releaseEvents()[0]);
        $payment->capture($this->at(1));
        self::assertSame(PaymentStatus::Captured, $payment->status());
        self::assertInstanceOf(PaymentCaptured::class, $payment->releaseEvents()[0]);
    }

    public function test_capture_without_authorization_is_refused_without_mutation(): void
    {
        $payment = Payment::authorize($this->paymentId(), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::Cash, TransactionReference::fromString('TX-000002'), $this->at(0));
        $payment->fail($this->at(1));
        $payment->releaseEvents();
        try {
            $payment->capture($this->at(2));
            self::fail('Capture must fail.');
        } catch (PaymentViolation) {
            self::assertSame(PaymentStatus::Failed, $payment->status());
            self::assertSame(2, $payment->version());
            self::assertSame([], $payment->releaseEvents());
        }
    }

    public function test_refund_before_capture_is_refused(): void
    {
        $payment = Payment::authorize($this->paymentId(), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::Cash, TransactionReference::fromString('TX-000003'), $this->at(0));
        $this->expectException(PaymentViolation::class);
        $payment->refund($this->at(1));
    }

    public function test_captured_payment_can_be_refunded_once(): void
    {
        $payment = Payment::authorize($this->paymentId(), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::Cash, TransactionReference::fromString('TX-000004'), $this->at(0));
        $payment->capture($this->at(1));
        $payment->refund($this->at(2));
        self::assertSame(PaymentStatus::Refunded, $payment->status());
        self::assertInstanceOf(PaymentRefunded::class, $payment->releaseEvents()[2]);
    }

    public function test_failed_payment_event_is_produced(): void
    {
        $payment = Payment::authorize($this->paymentId(), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::Cash, TransactionReference::fromString('TX-000005'), $this->at(0));
        $payment->fail($this->at(1));
        self::assertInstanceOf(PaymentFailed::class, $payment->releaseEvents()[1]);
    }

    public function test_benefit_requires_exact_captured_amount_proof(): void
    {
        $order = $this->order();
        $this->expectException(PaymentViolation::class);
        $payment = Payment::authorize($this->paymentId(), $this->orderId(), new Money(4999, Currency::XOF), PaymentMethod::Cash, TransactionReference::fromString('TX-900001'), $this->at(0));
        $payment->capture($this->at(1));
        $order->grantBenefit($payment->capturedProof(), $this->at(1));
    }

    public function test_benefit_grant_and_expiration_are_historized(): void
    {
        $order = $this->order();
        $order->grantBenefit($this->capturedPayment()->capturedProof(), $this->at(1));
        self::assertInstanceOf(BenefitGranted::class, $order->releaseEvents()[1]);
        $order->expireBenefit($this->at(31));
        self::assertSame(OrderStatus::BenefitExpired, $order->status());
        self::assertSame(BenefitStatus::Expired, $order->benefits()[0]->status);
        self::assertInstanceOf(BenefitExpired::class, $order->releaseEvents()[0]);
    }

    public function test_all_order_lines_receive_and_expire_their_benefit(): void
    {
        $first = $this->product(1);
        $second = $this->product(2);
        $order = Order::create($this->orderId(), [
            new OrderLine($first->id(), $first->price(), $first->benefitType(), $first->benefitDays()),
            new OrderLine($second->id(), $second->price(), $second->benefitType(), $second->benefitDays()),
        ], $this->at(0));
        $payment = Payment::authorize($this->paymentId(), $this->orderId(), new Money(10000, Currency::XOF), PaymentMethod::Cash, TransactionReference::fromString('TX-900002'), $this->at(0));
        $payment->capture($this->at(1));
        $order->grantBenefit($payment->capturedProof(), $this->at(1));
        self::assertCount(2, $order->benefits());
        $order->expireBenefit($this->at(31));
        self::assertSame(BenefitStatus::Expired, $order->benefits()[0]->status);
        self::assertSame(BenefitStatus::Expired, $order->benefits()[1]->status);
    }

    public function test_release_events_prevents_replay(): void
    {
        $order = $this->order();
        self::assertNotEmpty($order->releaseEvents());
        self::assertSame([], $order->releaseEvents());
    }
}
