<?php

namespace Appart\Modules\MonetizationPayments\Application\Contract;

use Appart\Modules\MonetizationPayments\Domain\Model\Product;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\ProductId;

interface ProductCatalog
{
    public function find(ProductId $id): ?Product;
}
