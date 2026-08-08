<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthResultV1;

interface ServiceHealthReaderV1
{
    public function read(ReliabilityOperationsObservedAt $observedAt): ServiceHealthResultV1;
}
