<?php

namespace Tests\Unit\Modules\MonetizationPayments;

use Appart\Modules\MonetizationPayments\Application\UseCase\AuthorizePayment;
use Appart\Modules\MonetizationPayments\Application\UseCase\CapturePayment;
use Appart\Modules\MonetizationPayments\Application\UseCase\CreateOrder;
use Appart\Modules\MonetizationPayments\Application\UseCase\ExpireBenefit;
use Appart\Modules\MonetizationPayments\Application\UseCase\GrantBenefit;
use Appart\Modules\MonetizationPayments\Application\UseCase\RefundPayment;
use Appart\Modules\MonetizationPayments\Domain\Exception\AggregateNotFound;
use Appart\Modules\MonetizationPayments\Domain\Exception\ConcurrentModification;
use Appart\Modules\MonetizationPayments\Domain\Exception\PaymentViolation;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentMethod;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;
use Tests\Unit\Modules\MonetizationPayments\Support\FakeOrderRegistry;
use Tests\Unit\Modules\MonetizationPayments\Support\FakePaymentRegistry;
use Tests\Unit\Modules\MonetizationPayments\Support\FakeProductCatalog;

final class MonetizationUseCasesTest extends MonetizationTestCase
{
    private FakeOrderRegistry $orders;

    private FakePaymentRegistry $payments;

    private FakeProductCatalog $products;

    protected function setUp(): void
    {
        $this->orders = new FakeOrderRegistry;
        $this->payments = new FakePaymentRegistry($this->orders);
        $this->products = new FakeProductCatalog;
        $this->products->add($this->product());
    }

    public function test_create_order_from_product_snapshot(): void
    {
        $order = (new CreateOrder($this->orders, $this->products))->execute($this->orderId(), [$this->productId()], $this->at(0));
        self::assertSame(5000, $order->total()->minorAmount);
        self::assertNotNull($this->orders->find($this->orderId()));
    }

    public function test_payment_without_order_is_refused(): void
    {
        $this->expectException(AggregateNotFound::class);
        (new AuthorizePayment($this->payments, $this->orders))->execute($this->paymentId(), $this->orderId(), PaymentMethod::MobileMoney, TransactionReference::fromString('TX-100001'), $this->at(0));
    }

    public function test_transaction_reference_replay_returns_existing_payment(): void
    {
        $this->createOrder();
        $authorize = new AuthorizePayment($this->payments, $this->orders);
        $reference = TransactionReference::fromString('TX-100002');
        $authorize->execute($this->paymentId(), $this->orderId(), PaymentMethod::MobileMoney, $reference, $this->at(0));
        $existing = $authorize->execute($this->paymentId(2), $this->orderId(), PaymentMethod::MobileMoney, $reference, $this->at(0));
        self::assertSame($this->paymentId()->value, $existing->id()->value);
    }

    public function test_failed_reference_reservation_rolls_back_completely(): void
    {
        $this->createOrder();
        $this->payments->failNextWrite = true;
        $authorize = new AuthorizePayment($this->payments, $this->orders);
        $reference = TransactionReference::fromString('TX-100003');
        try {
            $authorize->execute($this->paymentId(), $this->orderId(), PaymentMethod::Cash, $reference, $this->at(0));
            self::fail('Write must fail.');
        } catch (ConcurrentModification) {
            self::assertNull($this->payments->find($this->paymentId()));
        }
        $authorize->execute($this->paymentId(), $this->orderId(), PaymentMethod::Cash, $reference, $this->at(0));
        self::assertNotNull($this->payments->find($this->paymentId()));
    }

    public function test_save_failure_does_not_leak_payment_mutation(): void
    {
        $this->authorize();
        $this->payments->failNextWrite = true;
        try {
            (new CapturePayment($this->payments))->execute($this->paymentId(), $this->at(1));
            self::fail('Save must fail.');
        } catch (ConcurrentModification) {
            self::assertSame(PaymentStatus::Authorized, $this->payments->find($this->paymentId())?->status());
        }
    }

    public function test_optimistic_concurrency_rejects_stale_payment(): void
    {
        $this->authorize();
        $first = $this->payments->find($this->paymentId());
        $stale = $this->payments->find($this->paymentId());
        self::assertNotNull($first);
        self::assertNotNull($stale);
        $first->capture($this->at(1));
        $this->payments->save($first, 1);
        $stale->fail($this->at(1));
        $this->expectException(ConcurrentModification::class);
        $this->payments->save($stale, 1);
    }

    public function test_captured_payment_grants_then_expires_benefit(): void
    {
        $this->authorize();
        (new CapturePayment($this->payments))->execute($this->paymentId(), $this->at(1));
        (new GrantBenefit($this->orders, $this->payments))->execute($this->orderId(), $this->paymentId(), $this->at(1));
        self::assertSame(OrderStatus::Paid, $this->orders->find($this->orderId())->status());
        (new ExpireBenefit($this->orders))->execute($this->orderId(), $this->at(31));
        self::assertSame(OrderStatus::BenefitExpired, $this->orders->find($this->orderId())?->status());
    }

    public function test_refund_use_case_requires_capture(): void
    {
        $this->authorize();
        $this->expectException(PaymentViolation::class);
        (new RefundPayment($this->payments))->execute($this->paymentId(), $this->at(1));
    }

    private function createOrder(): void
    {
        (new CreateOrder($this->orders, $this->products))->execute($this->orderId(), [$this->productId()], $this->at(0));
    }

    private function authorize(): void
    {
        $this->createOrder();
        (new AuthorizePayment($this->payments, $this->orders))->execute($this->paymentId(), $this->orderId(), PaymentMethod::MobileMoney, TransactionReference::fromString('TX-200001'), $this->at(0));
    }
}
