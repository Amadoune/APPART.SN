<?php

namespace Tests\Unit\Modules\MonetizationPayments\Support;

use Appart\Modules\MonetizationPayments\Application\Contract\ProductCatalog;
use Appart\Modules\MonetizationPayments\Domain\Model\Product;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\ProductId;

final class FakeProductCatalog implements ProductCatalog
{
    /** @var array<string, Product> */
    private array $items = [];

    public function add(Product $product): void
    {
        $this->items[$product->id()->value] = clone $product;
    }

    public function find(ProductId $id): ?Product
    {
        return isset($this->items[$id->value]) ? clone $this->items[$id->value] : null;
    }
}
