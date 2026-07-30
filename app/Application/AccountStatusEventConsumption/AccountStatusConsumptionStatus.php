<?php

namespace App\Application\AccountStatusEventConsumption;

enum AccountStatusConsumptionStatus: string
{
    case Consumed = 'consumed';
    case Rejected = 'rejected';
}
