<?php

namespace Appart\Modules\Geography\Application\PlaceLifecyclePersistence;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use InvalidArgumentException;

final readonly class PlaceLifecycleStoredState
{
    public function __construct(
        public PlaceId $placeId,
        public PlaceLifecycleState $state,
        public int $version,
    ) {
        if ($version < 1) {
            throw new InvalidArgumentException('The stored place lifecycle version must be positive.');
        }
    }
}
