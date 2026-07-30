<?php

namespace Appart\Modules\MonetizationPayments\Application\Contract;

use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;

interface RefundTransaction
{
    public function findPayment(PaymentId $id): ?Payment;

    public function findOrder(OrderId $id): ?Order;

    /** Atomically persists both Aggregates or neither, conditional on both expected versions. */
    public function commitRefund(Payment $payment, int $expectedPaymentVersion, Order $order, int $expectedOrderVersion): void;
}
