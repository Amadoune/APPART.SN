<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case BenefitExpired = 'benefit_expired';
    case Refunded = 'refunded';
}
