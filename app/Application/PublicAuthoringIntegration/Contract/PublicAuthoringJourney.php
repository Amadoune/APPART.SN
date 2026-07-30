<?php

namespace App\Application\PublicAuthoringIntegration\Contract;

use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyRequest;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyResponse;

interface PublicAuthoringJourney
{
    public function execute(PublicAuthoringJourneyRequest $request): PublicAuthoringJourneyResponse;
}
