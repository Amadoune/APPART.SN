<?php

namespace Appart\Modules\MonetizationPayments\Application\UseCase;

use Appart\Modules\MonetizationPayments\Application\Contract\RefundTransaction;
use Appart\Modules\MonetizationPayments\Domain\Exception\AggregateNotFound;
use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;

final readonly class RefundPayment
{
    public function __construct(private RefundTransaction $transaction) {}

    public function execute(PaymentId $id, OccurredAt $at): Payment
    {
        $payment = $this->transaction->findPayment($id) ?? throw new AggregateNotFound('Payment not found.');
        $paymentVersion = $payment->version();
        $order = $this->transaction->findOrder($payment->orderId()) ?? throw new AggregateNotFound('Order not found.');
        $orderVersion = $order->version();
        $payment->refund($at);
        $order->revokeBenefitsAfterRefund($payment->refundedProof(), $at);
        $this->transaction->commitRefund($payment, $paymentVersion, $order, $orderVersion);

        return $payment;
    }
}
