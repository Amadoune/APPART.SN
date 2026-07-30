<?php

namespace App\Application\MediaItemLifecycleEventRouting;

final readonly class MediaItemLifecycleInboxStoreResult
{
    public function __construct(public MediaItemLifecycleInboxStoreStatus $status) {}
}
