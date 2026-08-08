<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ReleaseCandidateResultV1;

interface ReleaseCandidateReaderV1
{
    public function read(ExperienceAcceptanceObservedAt $observedAt): ReleaseCandidateResultV1;
}
