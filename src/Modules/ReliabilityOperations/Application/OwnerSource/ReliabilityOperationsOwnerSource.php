<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerSource;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

interface ReliabilityOperationsOwnerSource
{
    public function append(ReliabilityOperationsRevisionState $revision): ReliabilityOperationsWriteResult;

    public function read(ReliabilityOperationsScopeKey $scope, ReliabilityOperationsStream $stream, ReliabilityOperationsObservedAt $observedAt): ReliabilityOperationsReadResult;
}
