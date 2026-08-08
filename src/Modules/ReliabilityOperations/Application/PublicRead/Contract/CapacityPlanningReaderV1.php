<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

interface CapacityPlanningReaderV1
{
    public function read(ReliabilityOperationsObservedAt $observedAt): CapacityPlanningResultV1;
}
