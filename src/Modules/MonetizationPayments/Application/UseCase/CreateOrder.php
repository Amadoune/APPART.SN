<?php

namespace Appart\Modules\MonetizationPayments\Application\UseCase;

use Appart\Modules\MonetizationPayments\Application\Contract\OrderRegistry;
use Appart\Modules\MonetizationPayments\Application\Contract\ProductCatalog;
use Appart\Modules\MonetizationPayments\Domain\Exception\AggregateNotFound;
use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\Model\OrderLine;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\ProductId;

final readonly class CreateOrder
{
    public function __construct(private OrderRegistry $orders, private ProductCatalog $products) {}

    /** @param non-empty-list<ProductId> $productIds */
    public function execute(OrderId $id, array $productIds, OccurredAt $at): Order
    {
        $lines = [];
        foreach ($productIds as $productId) {
            $product = $this->products->find($productId) ?? throw new AggregateNotFound('Product not found.');
            $lines[] = new OrderLine($product->id(), $product->price(), $product->benefitType(), $product->benefitDays());
        }
        $order = Order::create($id, $lines, $at);
        $this->orders->add($order);

        return $order;
    }
}
