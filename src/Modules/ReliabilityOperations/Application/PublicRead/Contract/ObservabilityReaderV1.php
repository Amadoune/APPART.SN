<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

interface ObservabilityReaderV1
{
    public function read(ReliabilityOperationsObservedAt $observedAt): ObservabilityResultV1;
}
