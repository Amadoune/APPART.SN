<?php

namespace App\Application\PublicProjectionWorker\Contract;

use DateTimeImmutable;

interface PublicProjectionDeliveryClock
{
    public function now(): DateTimeImmutable;
}
