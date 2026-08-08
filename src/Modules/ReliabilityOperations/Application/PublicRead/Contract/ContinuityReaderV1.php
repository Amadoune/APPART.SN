<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

interface ContinuityReaderV1
{
    public function read(ReliabilityOperationsObservedAt $observedAt): ContinuityResultV1;
}
