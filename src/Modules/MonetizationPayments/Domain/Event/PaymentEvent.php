<?php

namespace Appart\Modules\MonetizationPayments\Domain\Event;

use Appart\Modules\MonetizationPayments\Domain\ValueObject\OccurredAt;

interface PaymentEvent
{
    public function occurredAt(): OccurredAt;
}
