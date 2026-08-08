<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

interface AlertingReaderV1
{
    public function read(ReliabilityOperationsObservedAt $observedAt): AlertingResultV1;
}
