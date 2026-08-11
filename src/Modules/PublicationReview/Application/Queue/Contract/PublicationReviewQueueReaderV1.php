<?php

namespace Appart\Modules\PublicationReview\Application\Queue\Contract;

use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueuePage;

interface PublicationReviewQueueReaderV1
{
    public function read(?string $afterCursor, int $limit): PublicationReviewQueuePage;
}
