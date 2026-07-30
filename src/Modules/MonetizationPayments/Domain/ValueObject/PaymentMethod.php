<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

enum PaymentMethod: string
{
    case MobileMoney = 'mobile_money';
    case BankCard = 'bank_card';
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';
}
