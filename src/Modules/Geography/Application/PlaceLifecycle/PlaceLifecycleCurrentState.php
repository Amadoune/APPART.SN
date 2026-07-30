<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycle;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

final readonly class PlaceLifecycleCurrentState
{
    public function __construct(
        public PlaceId $placeId,
        public PlaceLifecycleState $state,
    ) {}
}
