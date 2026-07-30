<?php

namespace App\Application\AccountStatusEventRouting;

use App\Application\AccountStatusEventTransport\AccountStatusDeliveryMessage;

interface AccountStatusEventRouter
{
    public function route(AccountStatusDeliveryMessage $message): AccountStatusRoutingResult;
}
