<?php

namespace App\Application\PropertyLifecycleEventRouting\Contract;

use App\Application\PropertyLifecycleEventRouting\PropertyLifecycleEventDestinationResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;

interface PropertyLifecycleEventDestination
{
    public function transfer(PropertyLifecycleEvent $event): PropertyLifecycleEventDestinationResult;
}
