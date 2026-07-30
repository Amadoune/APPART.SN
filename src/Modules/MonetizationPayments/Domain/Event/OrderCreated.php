<?php

namespace Appart\Modules\MonetizationPayments\Domain\Event;

use Appart\Modules\MonetizationPayments\Domain\Model\OrderLine;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\Money;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;

final readonly class OrderCreated implements PaymentEvent
{
    /** @param non-empty-list<OrderLine> $lines */
    public function __construct(public OrderId $orderId, public array $lines, public Money $total, public int $aggregateVersion, private OccurredAt $at) {}

    public function occurredAt(): OccurredAt
    {
        return $this->at;
    }
}
