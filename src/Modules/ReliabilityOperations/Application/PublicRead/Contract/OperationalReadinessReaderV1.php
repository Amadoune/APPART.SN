<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

interface OperationalReadinessReaderV1
{
    public function read(ReliabilityOperationsObservedAt $observedAt): OperationalReadinessResultV1;
}
