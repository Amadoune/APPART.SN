<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

final readonly class PropertyLifecycleTransition
{
    public function __construct(
        public PropertyLifecycleState $from,
        public PropertyLifecycleState $to,
        public PropertyLifecycleAction $action,
    ) {}
}
