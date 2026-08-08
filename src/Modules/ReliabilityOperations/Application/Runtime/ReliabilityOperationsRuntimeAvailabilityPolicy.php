<?php

namespace Appart\Modules\ReliabilityOperations\Application\Runtime;

interface ReliabilityOperationsRuntimeAvailabilityPolicy
{
    public function inspect(): ReliabilityOperationsRuntimeAvailability;
}
