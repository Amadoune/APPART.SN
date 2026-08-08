<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\EndToEndReadinessResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

interface EndToEndReadinessReaderV1
{
    public function read(ExperienceAcceptanceObservedAt $observedAt): EndToEndReadinessResultV1;
}
