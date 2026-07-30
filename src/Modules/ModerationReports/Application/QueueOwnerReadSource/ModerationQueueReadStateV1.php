<?php

namespace Appart\Modules\ModerationReports\Application\QueueOwnerReadSource;

enum ModerationQueueReadStateV1: string
{
    case Available = 'Available';
    case Claimed = 'Claimed';
    case Completed = 'Completed';
}
