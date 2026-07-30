<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

use Appart\Modules\MonetizationPayments\Domain\Model\Payment;

final readonly class RefundProof
{
    private function __construct(public PaymentId $paymentId, public OrderId $orderId, public TransactionReference $reference, public int $paymentVersion, public OccurredAt $refundedAt) {}

    public static function fromRefundedPayment(Payment $payment, OccurredAt $refundedAt): self
    {
        return new self($payment->id(), $payment->orderId(), $payment->reference(), $payment->version(), $refundedAt);
    }
}
