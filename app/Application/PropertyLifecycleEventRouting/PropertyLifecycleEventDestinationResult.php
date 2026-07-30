<?php

namespace App\Application\PropertyLifecycleEventRouting;

final readonly class PropertyLifecycleEventDestinationResult
{
    public function __construct(public PropertyLifecycleEventDestinationStatus $status) {}
}
