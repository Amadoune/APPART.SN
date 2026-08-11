<?php

namespace Appart\Modules\PublicationReview\Application\Queue\Contract;

interface PublicationReviewCommandLedger
{
    public function hasRecorded(string $commandId): bool;
}
