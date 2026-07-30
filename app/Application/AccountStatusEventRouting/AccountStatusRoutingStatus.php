<?php

namespace App\Application\AccountStatusEventRouting;

enum AccountStatusRoutingStatus: string
{
    case Routed = 'routed';
    case Rejected = 'rejected';
}
