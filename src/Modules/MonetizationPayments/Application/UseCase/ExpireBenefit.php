<?php

namespace Appart\Modules\MonetizationPayments\Application\UseCase;

use Appart\Modules\MonetizationPayments\Application\Contract\OrderRegistry;
use Appart\Modules\MonetizationPayments\Domain\Exception\AggregateNotFound;
use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;

final readonly class ExpireBenefit
{
    public function __construct(private OrderRegistry $orders) {}

    public function execute(OrderId $id, OccurredAt $at): Order
    {
        $order = $this->orders->find($id) ?? throw new AggregateNotFound('Order not found.');
        $version = $order->version();
        $order->expireBenefit($at);
        $this->orders->save($order, $version);

        return $order;
    }
}
