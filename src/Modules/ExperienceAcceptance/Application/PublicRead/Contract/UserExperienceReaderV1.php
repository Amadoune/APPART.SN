<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserExperienceResultV1;

interface UserExperienceReaderV1
{
    public function read(ExperienceAcceptanceObservedAt $observedAt): UserExperienceResultV1;
}
