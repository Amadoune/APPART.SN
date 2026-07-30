<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

enum ListingPublicationDecisionStatus: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
}
