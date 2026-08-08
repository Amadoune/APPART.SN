<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Consumer;

use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRoutingResult;

final readonly class DeterministicExperienceAcceptanceConsumer implements ExperienceAcceptanceConsumerV1
{
    public function __construct(private ExperienceAcceptanceConsumptionPolicy $policy) {}

    public function consume(ExperienceAcceptanceRoutingResult $routing): ExperienceAcceptanceConsumptionResult
    {
        $status = $this->policy->accepts($routing) ? ExperienceAcceptanceConsumptionStatus::Accepted : ExperienceAcceptanceConsumptionStatus::Rejected;

        return new ExperienceAcceptanceConsumptionResult($status, $routing);
    }
}
