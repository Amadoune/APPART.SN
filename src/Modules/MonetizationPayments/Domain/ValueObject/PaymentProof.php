<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

use Appart\Modules\MonetizationPayments\Domain\Model\Payment;

final readonly class PaymentProof
{
    private function __construct(public PaymentId $paymentId, public OrderId $orderId, public Money $amount, public TransactionReference $reference, public int $paymentVersion, public OccurredAt $capturedAt) {}

    public static function fromCapturedPayment(Payment $payment): self
    {
        return new self($payment->id(), $payment->orderId(), $payment->amount(), $payment->reference(), $payment->version(), $payment->capturedAt());
    }
}
