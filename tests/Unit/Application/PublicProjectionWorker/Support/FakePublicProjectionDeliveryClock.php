<?php

namespace Tests\Unit\Application\PublicProjectionWorker\Support;

use App\Application\PublicProjectionWorker\Contract\PublicProjectionDeliveryClock;
use DateTimeImmutable;

final class FakePublicProjectionDeliveryClock implements PublicProjectionDeliveryClock
{
    public function __construct(public DateTimeImmutable $current) {}

    public function now(): DateTimeImmutable
    {
        return $this->current;
    }
}
