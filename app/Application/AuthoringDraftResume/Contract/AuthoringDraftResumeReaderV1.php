<?php

namespace App\Application\AuthoringDraftResume\Contract;

use App\Application\AuthoringDraftResume\AuthoringDraftResumeResult;

interface AuthoringDraftResumeReaderV1
{
    public function read(string $accountId, string $listingId): AuthoringDraftResumeResult;
}
