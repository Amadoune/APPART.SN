<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserAcceptanceResultV1;

interface UserAcceptanceReaderV1
{
    public function read(ExperienceAcceptanceObservedAt $observedAt): UserAcceptanceResultV1;
}
