<?php

namespace Appart\Modules\MonetizationPayments\Domain\Model;

use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;
use Appart\Modules\MonetizationPayments\Domain\ValueObject\PaymentStatus;

final readonly class PaymentAttempt
{
    public function __construct(public int $sequence, public PaymentStatus $outcome, public OccurredAt $occurredAt) {}
}
