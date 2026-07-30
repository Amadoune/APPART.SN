<?php

namespace App\Application\AdministrativeActionLifecycleEventRouting;

final readonly class AdministrativeActionLifecycleInboxStoreResult
{
    public function __construct(public AdministrativeActionLifecycleInboxStoreStatus $status) {}
}
