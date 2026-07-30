<?php

namespace Appart\Modules\MonetizationPayments\Application\UseCase;

use Appart\Modules\MonetizationPayments\Application\Contract\OrderRegistry;
use Appart\Modules\MonetizationPayments\Application\Contract\PaymentRegistry;
use Appart\Modules\MonetizationPayments\Domain\Exception\AggregateNotFound;
use Appart\Modules\MonetizationPayments\Domain\Model\Payment;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentMethod;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;

final readonly class AuthorizePayment
{
    public function __construct(private PaymentRegistry $payments, private OrderRegistry $orders) {}

    public function execute(PaymentId $id, OrderId $orderId, PaymentMethod $method, TransactionReference $reference, OccurredAt $at): Payment
    {
        $order = $this->orders->find($orderId) ?? throw new AggregateNotFound('Order not found.');
        $payment = Payment::authorize($id, $orderId, $order->total(), $method, $reference, $at);

        return $this->payments->registerIdempotently($payment, $reference)->payment;
    }
}
