<?php

namespace Appart\Modules\MonetizationPayments\Application\Contract;

use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;

interface OrderRegistry
{
    /** Returns a detached aggregate reconstructed without previously published events. */
    public function find(OrderId $id): ?Order;

    public function add(Order $order): void;

    public function save(Order $order, int $expectedVersion): void;
}
/** Atomically adds a unique OrderId; nothing is visible on conflict. */
/** Saves a clean snapshot only when the stored version equals expectedVersion. */
