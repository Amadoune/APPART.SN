<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycle;

final readonly class PlaceLifecycleTransition
{
    public function __construct(
        public PlaceLifecycleState $from,
        public PlaceLifecycleAction $action,
        public PlaceLifecycleState $to,
    ) {}
}
