<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationIntent;

enum ListingModerationIntentReservationV1: string
{
    case Reserved = 'reserved';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
}
