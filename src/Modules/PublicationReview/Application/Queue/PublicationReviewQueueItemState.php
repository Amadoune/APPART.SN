<?php

namespace Appart\Modules\PublicationReview\Application\Queue;

enum PublicationReviewQueueItemState: string
{
    case Pending = 'pending';
    case Claimed = 'claimed';
    case Completed = 'completed';
}
