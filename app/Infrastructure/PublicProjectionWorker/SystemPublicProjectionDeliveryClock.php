<?php

namespace App\Infrastructure\PublicProjectionWorker;

use App\Application\PublicProjectionWorker\Contract\PublicProjectionDeliveryClock;
use DateTimeImmutable;

final readonly class SystemPublicProjectionDeliveryClock implements PublicProjectionDeliveryClock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
