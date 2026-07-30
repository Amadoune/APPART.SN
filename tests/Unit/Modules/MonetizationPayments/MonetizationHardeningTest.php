<?php

namespace Tests\Unit\Modules\MonetizationPayments;

use Appart\Modules\MonetizationPayments\Application\UseCase\AuthorizePayment;
use Appart\Modules\MonetizationPayments\Application\UseCase\CapturePayment;
use Appart\Modules\MonetizationPayments\Application\UseCase\CreateOrder;
use Appart\Modules\MonetizationPayments\Application\UseCase\GrantBenefit;
use Appart\Modules\MonetizationPayments\Application\UseCase\RefundPayment;
use Appart\Modules\MonetizationPayments\Domain\Event\BenefitGranted;
use Appart\Modules\MonetizationPayments\Domain\Event\BenefitRevoked;
use Appart\Modules\MonetizationPayments\Domain\Event\OrderCreated;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentAuthorized;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentCaptured;
use Appart\Modules\MonetizationPayments\Domain\Event\PaymentRefunded;
use Appart\Modules\MonetizationPayments\Domain\Exception\ConcurrentModification;
use Appart\Modules\MonetizationPayments\Domain\Exception\PaymentViolation;
use Appart\Modules\MonetizationPayments\Domain\Exception\TransactionReferenceConflict;
use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\Model\OrderLine;
use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\Model\Product;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Currency;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Money;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentMethod;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentRegistrationStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;
use Tests\Unit\Modules\MonetizationPayments\Support\FakeOrderRegistry;
use Tests\Unit\Modules\MonetizationPayments\Support\FakePaymentRegistry;
use Tests\Unit\Modules\MonetizationPayments\Support\FakeProductCatalog;

final class MonetizationHardeningTest extends MonetizationTestCase
{
    public function test_product_reconstruction_restores_history_without_events(): void
    {
        $original = $this->product();
        $restored = Product::reconstitute($original->id(), $original->name(), $original->price(), $original->benefitType(), $original->benefitDays(), $original->version(), $original->history());
        self::assertSame($original->id()->value, $restored->id()->value);
        self::assertSame(1, $restored->version());
        self::assertCount(1, $restored->history());
        self::assertSame([], $restored->releaseEvents());
    }

    public function test_order_reconstruction_restores_state_without_events(): void
    {
        $original = $this->order();
        $restored = Order::reconstitute($original->id(), $original->lines(), $original->total(), $original->status(), $this->at(0), $original->version(), $original->benefits(), $original->history());
        self::assertSame(OrderStatus::PendingPayment, $restored->status());
        self::assertSame(1, $restored->version());
        self::assertSame([], $restored->releaseEvents());
    }

    public function test_payment_reconstruction_restores_history_without_events(): void
    {
        $original = $this->capturedPayment();
        $restored = Payment::reconstitute($original->id(), $original->orderId(), $original->amount(), $original->method(), $original->reference(), $original->status(), $this->at(1), $original->version(), $original->attempts());
        self::assertSame(PaymentStatus::Captured, $restored->status());
        self::assertCount(2, $restored->attempts());
        self::assertSame(2, $restored->version());
        self::assertSame([], $restored->releaseEvents());
    }

    public function test_empty_order_is_refused_without_event(): void
    {
        $this->expectException(PaymentViolation::class);
        Order::create($this->orderId(), [], $this->at(0));
    }

    public function test_duplicate_product_line_is_refused(): void
    {
        $product = $this->product();
        $line = new OrderLine($product->id(), $product->price(), $product->benefitType(), $product->benefitDays());
        $this->expectException(PaymentViolation::class);
        Order::create($this->orderId(), [$line, $line], $this->at(0));
    }

    public function test_idempotent_replay_returns_existing_payment_without_replayed_events(): void
    {
        [$orders, $payments, $products] = $this->stores();
        $this->createOrder($orders, $products);
        $useCase = new AuthorizePayment($payments, $orders);
        $reference = TransactionReference::fromString('IDEMP-0001');
        $created = $useCase->execute($this->paymentId(), $this->orderId(), PaymentMethod::MobileMoney, $reference, $this->at(0));
        $created->releaseEvents();
        $existing = $useCase->execute($this->paymentId(), $this->orderId(), PaymentMethod::MobileMoney, $reference, $this->at(0));
        self::assertSame($created->id()->value, $existing->id()->value);
        self::assertSame([], $existing->releaseEvents());
    }

    public function test_same_reference_with_different_intention_conflicts(): void
    {
        [$orders, $payments, $products] = $this->stores();
        $this->createOrder($orders, $products);
        $useCase = new AuthorizePayment($payments, $orders);
        $reference = TransactionReference::fromString('IDEMP-0002');
        $useCase->execute($this->paymentId(), $this->orderId(), PaymentMethod::MobileMoney, $reference, $this->at(0));
        $this->expectException(TransactionReferenceConflict::class);
        $useCase->execute($this->paymentId(2), $this->orderId(), PaymentMethod::Cash, $reference, $this->at(0));
    }

    public function test_atomic_registration_reports_created_then_existing_idempotent(): void
    {
        $payments = new FakePaymentRegistry;
        $reference = TransactionReference::fromString('IDEMP-0003');
        $candidate = Payment::authorize($this->paymentId(), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::MobileMoney, $reference, $this->at(0));
        $created = $payments->registerIdempotently($candidate, $reference);
        $replayed = $payments->registerIdempotently(Payment::authorize($this->paymentId(), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::MobileMoney, $reference, $this->at(0)), $reference);
        self::assertSame(PaymentRegistrationStatus::Created, $created->status);
        self::assertSame(PaymentRegistrationStatus::ExistingIdempotent, $replayed->status);
        self::assertSame([], $replayed->payment->releaseEvents());
    }

    public function test_idempotent_replay_performs_no_second_write(): void
    {
        $payments = new FakePaymentRegistry;
        $reference = TransactionReference::fromString('IDEMP-0004');
        $candidate = Payment::authorize($this->paymentId(), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::Cash, $reference, $this->at(0));
        $payments->registerIdempotently($candidate, $reference);
        $payments->failNextWrite = true;
        $replayed = $payments->registerIdempotently(Payment::authorize($this->paymentId(2), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::Cash, $reference, $this->at(0)), $reference);
        self::assertSame(PaymentRegistrationStatus::ExistingIdempotent, $replayed->status);
        self::assertSame($this->paymentId()->value, $replayed->payment->id()->value);
    }

    public function test_payment_proof_is_impossible_before_capture_and_complete_after_capture(): void
    {
        $payment = Payment::authorize($this->paymentId(), $this->orderId(), new Money(5000, Currency::XOF), PaymentMethod::Cash, TransactionReference::fromString('PROOF-0001'), $this->at(0));
        try {
            $payment->capturedProof();
            self::fail('Proof must fail before capture.');
        } catch (PaymentViolation) {
            self::assertSame([], array_slice($payment->releaseEvents(), 1));
        }
        $payment->capture($this->at(1));
        $proof = $payment->capturedProof();
        self::assertSame($payment->id()->value, $proof->paymentId->value);
        self::assertSame(5000, $proof->amount->minorAmount);
        self::assertSame(Currency::XOF, $proof->amount->currency);
        self::assertSame(2, $proof->paymentVersion);
        self::assertEquals($this->at(1)->value, $proof->capturedAt->value);
    }

    public function test_foreign_payment_proof_is_refused_without_mutation_or_event(): void
    {
        $order = $this->order();
        $payment = Payment::authorize($this->paymentId(), $this->orderId(2), new Money(5000, Currency::XOF), PaymentMethod::Cash, TransactionReference::fromString('PROOF-0002'), $this->at(0));
        $payment->capture($this->at(1));
        $order->releaseEvents();
        try {
            $order->grantBenefit($payment->capturedProof(), $this->at(1));
            self::fail('Foreign proof must fail.');
        } catch (PaymentViolation) {
            self::assertSame(OrderStatus::PendingPayment, $order->status());
            self::assertSame(1, $order->version());
            self::assertSame([], $order->releaseEvents());
        }
    }

    public function test_refund_atomically_revokes_every_benefit_and_updates_both_aggregates(): void
    {
        [$orders, $payments, $products] = $this->stores();
        $this->createOrder($orders, $products);
        $this->authorizeCaptureAndGrant($orders, $payments);
        (new RefundPayment($payments))->execute($this->paymentId(), $this->at(2));
        $payment = $payments->find($this->paymentId());
        $order = $orders->find($this->orderId());
        self::assertNotNull($payment);
        self::assertNotNull($order);
        self::assertSame(PaymentStatus::Refunded, $payment->status());
        self::assertSame(OrderStatus::Refunded, $order->status());
        self::assertSame(BenefitStatus::Revoked, $order->benefits()[0]->status);
    }

    public function test_refund_invalidates_captured_proof_and_double_refund_is_refused_without_events(): void
    {
        [$orders, $payments, $products] = $this->stores();
        $this->createOrder($orders, $products);
        $this->authorizeCaptureAndGrant($orders, $payments);
        (new RefundPayment($payments))->execute($this->paymentId(), $this->at(2));
        self::assertNull($payments->capturedProof($this->paymentId()));
        try {
            (new RefundPayment($payments))->execute($this->paymentId(), $this->at(3));
            self::fail('Double refund must fail.');
        } catch (PaymentViolation) {
            $payment = $payments->find($this->paymentId());
            self::assertNotNull($payment);
            self::assertSame(PaymentStatus::Refunded, $payment->status());
            self::assertSame([], $payment->releaseEvents());
        }
    }

    public function test_captured_payment_can_be_refunded_before_benefit_grant(): void
    {
        [$orders, $payments, $products] = $this->stores();
        $this->createOrder($orders, $products);
        (new AuthorizePayment($payments, $orders))->execute($this->paymentId(), $this->orderId(), PaymentMethod::Cash, TransactionReference::fromString('REFUND-002'), $this->at(0));
        (new CapturePayment($payments))->execute($this->paymentId(), $this->at(1));
        (new RefundPayment($payments))->execute($this->paymentId(), $this->at(2));
        $order = $orders->find($this->orderId());
        self::assertNotNull($order);
        self::assertSame(OrderStatus::Refunded, $order->status());
        self::assertSame([], $order->benefits());
    }

    public function test_expiration_after_refund_revocation_is_refused(): void
    {
        [$orders, $payments, $products] = $this->stores();
        $this->createOrder($orders, $products);
        $this->authorizeCaptureAndGrant($orders, $payments);
        (new RefundPayment($payments))->execute($this->paymentId(), $this->at(2));
        $order = $orders->find($this->orderId());
        self::assertNotNull($order);
        $order->releaseEvents();
        try {
            $order->expireBenefit($this->at(31));
            self::fail('Revoked benefit cannot expire.');
        } catch (PaymentViolation) {
            self::assertSame(OrderStatus::Refunded, $order->status());
            self::assertSame([], $order->releaseEvents());
        }
    }

    public function test_benefit_revoked_event_contains_relation_reference_and_version(): void
    {
        $order = $this->order();
        $payment = $this->capturedPayment();
        $order->grantBenefit($payment->capturedProof(), $this->at(1));
        $order->releaseEvents();
        $payment->refund($this->at(2));
        $order->revokeBenefitsAfterRefund($payment->refundedProof(), $this->at(2));
        $event = $order->releaseEvents()[0];
        self::assertInstanceOf(BenefitRevoked::class, $event);
        self::assertSame($payment->id()->value, $event->paymentId->value);
        self::assertSame($payment->reference()->value, $event->reference->value);
        self::assertSame(3, $event->aggregateVersion);
        $refunded = $payment->releaseEvents()[2];
        self::assertInstanceOf(PaymentRefunded::class, $refunded);
        self::assertSame($order->id()->value, $refunded->orderId->value);
        self::assertSame(5000, $refunded->amount->minorAmount);
        self::assertSame(Currency::XOF, $refunded->amount->currency);
        self::assertSame(3, $refunded->aggregateVersion);
    }

    public function test_payment_write_failure_rolls_back_refund_completely(): void
    {
        [$orders, $payments, $products] = $this->stores();
        $this->createOrder($orders, $products);
        $this->authorizeCaptureAndGrant($orders, $payments);
        $payments->failNextWrite = true;
        try {
            (new RefundPayment($payments))->execute($this->paymentId(), $this->at(2));
            self::fail('Refund must fail.');
        } catch (ConcurrentModification) {
            $this->assertRefundWasRolledBack($orders, $payments);
        }
    }

    public function test_order_write_failure_rolls_back_refund_completely(): void
    {
        [$orders, $payments, $products] = $this->stores();
        $this->createOrder($orders, $products);
        $this->authorizeCaptureAndGrant($orders, $payments);
        $orders->failNextWrite = true;
        try {
            (new RefundPayment($payments))->execute($this->paymentId(), $this->at(2));
            self::fail('Refund must fail.');
        } catch (ConcurrentModification) {
            $this->assertRefundWasRolledBack($orders, $payments);
        }
    }

    public function test_enriched_events_contain_financial_proof_and_versions(): void
    {
        $order = $this->order();
        $orderCreated = $order->releaseEvents()[0];
        self::assertInstanceOf(OrderCreated::class, $orderCreated);
        self::assertSame(1, $orderCreated->aggregateVersion);
        self::assertCount(1, $orderCreated->lines);
        $payment = $this->capturedPayment();
        $events = $payment->releaseEvents();
        self::assertInstanceOf(PaymentAuthorized::class, $events[0]);
        self::assertInstanceOf(PaymentCaptured::class, $events[1]);
        self::assertSame(5000, $events[1]->amount->minorAmount);
        self::assertSame(2, $events[1]->aggregateVersion);
        $order->grantBenefit($payment->capturedProof(), $this->at(1));
        $benefit = $order->releaseEvents()[0];
        self::assertInstanceOf(BenefitGranted::class, $benefit);
        self::assertSame($payment->reference()->value, $benefit->reference->value);
    }

    /** @return array{FakeOrderRegistry, FakePaymentRegistry, FakeProductCatalog} */
    private function stores(): array
    {
        $orders = new FakeOrderRegistry;

        return [$orders, new FakePaymentRegistry($orders), new FakeProductCatalog];
    }

    private function createOrder(FakeOrderRegistry $orders, FakeProductCatalog $products): void
    {
        $products->add($this->product());
        (new CreateOrder($orders, $products))->execute($this->orderId(), [$this->productId()], $this->at(0));
    }

    private function authorizeCaptureAndGrant(FakeOrderRegistry $orders, FakePaymentRegistry $payments): void
    {
        (new AuthorizePayment($payments, $orders))->execute($this->paymentId(), $this->orderId(), PaymentMethod::MobileMoney, TransactionReference::fromString('REFUND-001'), $this->at(0));
        (new CapturePayment($payments))->execute($this->paymentId(), $this->at(1));
        (new GrantBenefit($orders, $payments))->execute($this->orderId(), $this->paymentId(), $this->at(1));
    }

    private function assertRefundWasRolledBack(FakeOrderRegistry $orders, FakePaymentRegistry $payments): void
    {
        $payment = $payments->find($this->paymentId());
        $order = $orders->find($this->orderId());
        self::assertNotNull($payment);
        self::assertNotNull($order);
        self::assertSame(PaymentStatus::Captured, $payment->status());
        self::assertSame(OrderStatus::Paid, $order->status());
        self::assertSame(BenefitStatus::Granted, $order->benefits()[0]->status);
        self::assertSame([], $payment->releaseEvents());
        self::assertSame([], $order->releaseEvents());
    }
}
