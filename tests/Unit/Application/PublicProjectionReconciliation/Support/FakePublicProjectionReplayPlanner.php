<?php

namespace Tests\Unit\Application\PublicProjectionReconciliation\Support;

use App\Application\PublicProjectionReconciliation\Contract\PublicProjectionReplayPlanner;
use App\Application\PublicProjectionRetry\PublicProjectionReplayRequest;

final class FakePublicProjectionReplayPlanner implements PublicProjectionReplayPlanner
{
    /** @var list<PublicProjectionReplayRequest> */
    public array $requests = [];

    public function schedule(PublicProjectionReplayRequest $request): void
    {
        $this->requests[] = $request;
    }
}
