<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Consumer;

enum ExperienceAcceptanceConsumptionStatus: string
{
    case Accepted = 'accepted';
    case Rejected = 'rejected';
}
