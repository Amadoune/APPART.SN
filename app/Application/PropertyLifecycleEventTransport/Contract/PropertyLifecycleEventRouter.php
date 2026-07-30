<?php

namespace App\Application\PropertyLifecycleEventTransport\Contract;

use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;

interface PropertyLifecycleEventRouter
{
    public function route(PropertyLifecycleEvent $event): PropertyLifecycleEventRoutingResult;
}
