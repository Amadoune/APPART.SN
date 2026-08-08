<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Routing;

use Appart\Modules\ExperienceAcceptance\Application\Transport\ExperienceAcceptanceTransportEnvelope;

interface ExperienceAcceptanceRouterV1
{
    public function route(ExperienceAcceptanceTransportEnvelope $envelope): ExperienceAcceptanceRoutingResult;
}
