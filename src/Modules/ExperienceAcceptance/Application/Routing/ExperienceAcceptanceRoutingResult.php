<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Routing;

use Appart\Modules\ExperienceAcceptance\Application\Transport\ExperienceAcceptanceTransportEnvelope;

final readonly class ExperienceAcceptanceRoutingResult
{
    public function __construct(
        public ExperienceAcceptanceRoutingStatus $status,
        public ExperienceAcceptanceRouteDestination $destination,
        public ExperienceAcceptanceTransportEnvelope $envelope,
    ) {}
}
