<?php

namespace App\Application\AccountStatusEventRouting;

enum AccountStatusRoutingDestination: string
{
    case LifecycleFacts = 'identity_access.account_status.lifecycle_facts';
}
