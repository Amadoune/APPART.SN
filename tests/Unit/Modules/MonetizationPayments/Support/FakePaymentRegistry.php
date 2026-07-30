<?php

namespace Tests\Unit\Modules\MonetizationPayments\Support;

use Appart\Modules\MonetizationPayments\Application\Contract\PaymentCatalog;
use Appart\Modules\MonetizationPayments\Application\Contract\PaymentRegistry;
use Appart\Modules\MonetizationPayments\Application\Contract\RefundTransaction;
use Appart\Modules\MonetizationPayments\Domain\Exception\ConcurrentModification;
use Appart\Modules\MonetizationPayments\Domain\Exception\IdentityConflict;
use Appart\Modules\MonetizationPayments\Domain\Exception\PaymentViolation;
use Appart\Modules\MonetizationPayments\Domain\Exception\TransactionReferenceConflict;
use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentProof;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentRegistration;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentRegistrationStatus;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;

final class FakePaymentRegistry implements PaymentCatalog, PaymentRegistry, RefundTransaction
{
    /** @var array<string, Payment> */
    private array $items = [];

    /** @var array<string, string> */
    private array $references = [];

    public bool $failNextWrite = false;

    public function __construct(private readonly ?FakeOrderRegistry $orders = null) {}

    public function find(PaymentId $id): ?Payment
    {
        return isset($this->items[$id->value]) ? clone $this->items[$id->value] : null;
    }

    public function registerIdempotently(Payment $payment, TransactionReference $reference): PaymentRegistration
    {
        if (isset($this->references[$reference->value])) {
            $existing = $this->items[$this->references[$reference->value]];
            if ($existing->hasSameIntentAs($payment)) {
                return new PaymentRegistration(clone $existing, PaymentRegistrationStatus::ExistingIdempotent);
            }
            throw new TransactionReferenceConflict;
        }
        $this->guard();
        if (isset($this->items[$payment->id()->value])) {
            throw new IdentityConflict;
        }
        $this->items[$payment->id()->value] = $this->clean($payment);
        $this->references[$reference->value] = $payment->id()->value;

        return new PaymentRegistration($payment, PaymentRegistrationStatus::Created);
    }

    public function capturedProof(PaymentId $id): ?PaymentProof
    {
        $payment = $this->find($id);
        if ($payment === null) {
            return null;
        }
        try {
            return $payment->capturedProof();
        } catch (PaymentViolation) {
            return null;
        }
    }

    public function findPayment(PaymentId $id): ?Payment
    {
        return $this->find($id);
    }

    public function findOrder(OrderId $id): ?Order
    {
        return $this->orders?->find($id);
    }

    public function commitRefund(Payment $payment, int $expectedPaymentVersion, Order $order, int $expectedOrderVersion): void
    {
        $this->guard();
        $stored = $this->items[$payment->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedPaymentVersion || $this->orders === null) {
            throw new ConcurrentModification;
        }
        $this->orders->assertCanSave($order, $expectedOrderVersion);
        $this->items[$payment->id()->value] = $this->clean($payment);
        $this->orders->commitValidated($order);
    }

    public function save(Payment $payment, int $expectedVersion): void
    {
        $this->guard();
        $stored = $this->items[$payment->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentModification;
        } $this->items[$payment->id()->value] = $this->clean($payment);
    }

    private function guard(): void
    {
        if ($this->failNextWrite) {
            $this->failNextWrite = false;
            throw new ConcurrentModification;
        }
    }

    private function clean(Payment $payment): Payment
    {
        $copy = clone $payment;
        $copy->releaseEvents();

        return $copy;
    }
}
