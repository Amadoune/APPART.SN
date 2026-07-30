<?php

namespace App\Application\ModerationAtomicOperation\Contract;

use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;

interface ModerationOutboxAppenderV1
{
    public function append(ModerationDeliveryMessageV1 $message): ModerationOutboxAppendResult;
}
