<?php

namespace Appart\Modules\MonetizationPayments\Domain\Model;

use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderStatus;

final readonly class OrderHistoryEntry
{
    public function __construct(public OrderStatus $status, public OccurredAt $occurredAt, public int $version) {}
}
