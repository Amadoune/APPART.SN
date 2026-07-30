<?php

namespace App\Application\PublicProjectionReconciliation\Contract;

use App\Application\PublicProjectionRetry\PublicProjectionReplayRequest;

interface PublicProjectionReplayPlanner
{
    public function schedule(PublicProjectionReplayRequest $request): void;
}
