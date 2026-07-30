<?php

namespace Appart\Modules\MonetizationPayments\Application\UseCase;

use Appart\Modules\MonetizationPayments\Application\Contract\OrderRegistry;
use Appart\Modules\MonetizationPayments\Application\Contract\PaymentCatalog;
use Appart\Modules\MonetizationPayments\Domain\Exception\AggregateNotFound;
use Appart\Modules\MonetizationPayments\Domain\Exception\PaymentViolation;
use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;

final readonly class GrantBenefit
{
    public function __construct(private OrderRegistry $orders, private PaymentCatalog $payments) {}

    public function execute(OrderId $orderId, PaymentId $paymentId, OccurredAt $at): Order
    {
        $order = $this->orders->find($orderId) ?? throw new AggregateNotFound('Order not found.');
        $version = $order->version();
        $proof = $this->payments->capturedProof($paymentId) ?? throw new AggregateNotFound('Captured payment proof not found.');
        if ($proof->orderId->value !== $orderId->value) {
            throw new PaymentViolation('Captured payment proof does not match order.');
        }
        $order->grantBenefit($proof, $at);
        $this->orders->save($order, $version);

        return $order;
    }
}
