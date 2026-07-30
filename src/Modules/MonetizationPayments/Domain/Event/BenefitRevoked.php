<?php

namespace Appart\Modules\MonetizationPayments\Domain\Event;

use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitPeriod;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\BenefitType;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\OrderId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentId;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\TransactionReference;

final readonly class BenefitRevoked implements PaymentEvent
{
    public function __construct(public OrderId $orderId, public PaymentId $paymentId, public TransactionReference $reference, public BenefitType $type, public BenefitPeriod $period, public int $aggregateVersion, private OccurredAt $at) {}

    public function occurredAt(): OccurredAt
    {
        return $this->at;
    }
}
