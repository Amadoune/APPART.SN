<?php

namespace Appart\Modules\MonetizationPayments\Domain\Event;

use Appart\Modules\MonetizationPayments\Domain\ValueObject\Money;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;

final readonly class PaymentCaptured implements PaymentEvent
{
    public function __construct(public PaymentId $paymentId, public OrderId $orderId, public Money $amount, public TransactionReference $reference, public int $aggregateVersion, private OccurredAt $at) {}

    public function occurredAt(): OccurredAt
    {
        return $this->at;
    }
}
