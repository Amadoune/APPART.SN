<?php

namespace App\Application\PlaceLifecycleEventRouting;

final readonly class PlaceLifecycleInboxStoreResult
{
    public function __construct(public PlaceLifecycleInboxStoreStatus $status) {}
}
