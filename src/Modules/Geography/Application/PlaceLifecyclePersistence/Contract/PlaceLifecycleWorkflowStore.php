<?php

namespace Appart\Modules\Geography\Application\PlaceLifecyclePersistence\Contract;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecyclePersistenceReadResult;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecyclePersistenceWriteResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

interface PlaceLifecycleWorkflowStore
{
    public function initialize(
        PlaceId $placeId,
        PlaceLifecycleState $state,
        int $version,
    ): PlaceLifecyclePersistenceWriteResult;

    public function append(
        PlaceLifecycleTransition $transition,
        PlaceMergeContextV1 $context,
    ): PlaceLifecyclePersistenceWriteResult;

    public function read(PlaceId $placeId): PlaceLifecyclePersistenceReadResult;
}
