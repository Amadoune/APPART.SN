<?php

namespace Tests\Unit\Modules\MonetizationPayments\Support;

use Appart\Modules\MonetizationPayments\Application\Contract\OrderRegistry;
use Appart\Modules\MonetizationPayments\Domain\Exception\ConcurrentModification;
use Appart\Modules\MonetizationPayments\Domain\Exception\IdentityConflict;
use Appart\Modules\MonetizationPayments\Domain\Model\Order;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;

final class FakeOrderRegistry implements OrderRegistry
{
    /** @var array<string, Order> */
    private array $items = [];

    public bool $failNextWrite = false;

    public function find(OrderId $id): ?Order
    {
        return isset($this->items[$id->value]) ? clone $this->items[$id->value] : null;
    }

    public function add(Order $order): void
    {
        $this->guard();
        if (isset($this->items[$order->id()->value])) {
            throw new IdentityConflict;
        } $this->items[$order->id()->value] = $this->clean($order);
    }

    public function save(Order $order, int $expectedVersion): void
    {
        $this->guard();
        $stored = $this->items[$order->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentModification;
        } $this->items[$order->id()->value] = $this->clean($order);
    }

    public function assertCanSave(Order $order, int $expectedVersion): void
    {
        $this->guard();
        $stored = $this->items[$order->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentModification;
        }
    }

    public function commitValidated(Order $order): void
    {
        $this->items[$order->id()->value] = $this->clean($order);
    }

    private function guard(): void
    {
        if ($this->failNextWrite) {
            $this->failNextWrite = false;
            throw new ConcurrentModification;
        }
    }

    private function clean(Order $order): Order
    {
        $copy = clone $order;
        $copy->releaseEvents();

        return $copy;
    }
}
