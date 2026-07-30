<?php

namespace Tests\Unit\Application\PublicProjectionRetry\Support;

use App\Application\PublicProjectionRetry\PublicProjectionReplayRequest;

final class FakePublicProjectionReplay
{
    /** @var list<PublicProjectionReplayRequest> */
    public array $requests = [];

    public function schedule(PublicProjectionReplayRequest $request): void
    {
        $this->requests[] = $request;
    }
}
