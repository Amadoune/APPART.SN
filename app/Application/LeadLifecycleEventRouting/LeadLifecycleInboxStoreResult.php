<?php

namespace App\Application\LeadLifecycleEventRouting;

final readonly class LeadLifecycleInboxStoreResult
{
    public function __construct(public LeadLifecycleInboxStoreStatus $status) {}
}
