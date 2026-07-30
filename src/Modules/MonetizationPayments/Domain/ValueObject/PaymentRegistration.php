<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

use Appart\Modules\MonetizationPayments\Domain\Model\Payment;

final readonly class PaymentRegistration
{
    public function __construct(public Payment $payment, public PaymentRegistrationStatus $status) {}
}
