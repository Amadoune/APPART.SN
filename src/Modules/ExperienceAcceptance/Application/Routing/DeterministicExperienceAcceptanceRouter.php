<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Routing;

use Appart\Modules\ExperienceAcceptance\Application\Transport\ExperienceAcceptanceTransportEnvelope;

final readonly class DeterministicExperienceAcceptanceRouter implements ExperienceAcceptanceRouterV1
{
    public function route(ExperienceAcceptanceTransportEnvelope $envelope): ExperienceAcceptanceRoutingResult
    {
        $destination = ExperienceAcceptanceRouteDestination::from(str_replace('.observed', '', $envelope->type->value));

        return new ExperienceAcceptanceRoutingResult(ExperienceAcceptanceRoutingStatus::Routed, $destination, $envelope);
    }
}
