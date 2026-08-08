<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

interface MaintenanceOperationsReaderV1
{
    public function read(ReliabilityOperationsObservedAt $observedAt): MaintenanceOperationsResultV1;
}
